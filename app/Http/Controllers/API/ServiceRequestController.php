<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ServiceRequestController extends Controller
{
    // List requests with role-based scope
    public function index(Request $request)
    {
        $user = $request->user();

        $query = ServiceRequest::with(['resident','assignedWorker']);

        if ($user->isResident()) {
            $query->where('resident_id', $user->id);
        } elseif ($user->isServiceWorker()) {
            $query->where(function($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('status','pending'); // workers can also view unassigned if you want
            });
        } // admin sees all

        $requests = $query->orderBy('created_at','desc')->paginate(20);
        return response()->json($requests);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'=>'required|string|max:255',
            'description'=>'sometimes|string',
            'address'=>'sometimes|string',
            'photo'=>'sometimes|file|image|max:8192',
            'priority'=>['sometimes', 'in:low,medium,high']
        ]);

        $data['resident_id'] = $request->user()->id;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('requests','public');
        }

        $sr = ServiceRequest::create($data);
        return response()->json($sr->load(['resident','assignedWorker']), 201);
    }

    public function show(ServiceRequest $serviceRequest)
    {
        return response()->json($serviceRequest->load(['resident','assignedWorker']));
    }

    public function update(Request $request, ServiceRequest $serviceRequest)
    {
        $user = $request->user();

        // Residents can update their request only if pending, admin can update any, worker may update status
        $rules = [
            'title'=>'sometimes|string|max:255',
            'description'=>'sometimes|string',
            'address'=>'sometimes|string',
            'photo'=>'sometimes|file|image|max:8192',
            'priority'=>['sometimes', 'in:low,medium,high'],
            'status'=>['sometimes','in:pending,in_progress,completed,cancelled']
        ];

        $data = $request->validate($rules);

        if ($request->hasFile('photo')) {
            if ($serviceRequest->photo) Storage::disk('public')->delete($serviceRequest->photo);
            $data['photo'] = $request->file('photo')->store('requests','public');
        }

        // Permission checks:
        if ($user->isResident() && $serviceRequest->resident_id !== $user->id) {
            return response()->json(['message'=>'Forbidden'],403);
        }

        // If resident tries to change status -> block (unless admin)
        if ($user->isResident() && isset($data['status'])) {
            return response()->json(['message'=>'Residents cannot change status'],403);
        }

        // Workers can only change status or add notes (for simplicity allow title/description updates too)
        if ($user->isServiceWorker() && $serviceRequest->assigned_to !== $user->id && isset($data['status'])) {
            return response()->json(['message'=>'You are not assigned to this request'],403);
        }

        $serviceRequest->update($data);
        return response()->json($serviceRequest->fresh()->load(['resident','assignedWorker']));
    }

    public function destroy(Request $request, ServiceRequest $serviceRequest)
    {
        $user = $request->user();
        if ($user->isResident() && $serviceRequest->resident_id !== $user->id) {
            return response()->json(['message'=>'Forbidden'],403);
        }
        if ($serviceRequest->photo) Storage::disk('public')->delete($serviceRequest->photo);
        $serviceRequest->delete();
        return response()->json(['message'=>'Service request deleted']);
    }

    // Admin route to assign a worker
    public function assign(Request $request, ServiceRequest $serviceRequest)
    {
        $data = $request->validate([
            'worker_id'=>'required|exists:users,id'
        ]);

        $worker = User::find($data['worker_id']);
        if (!$worker->isServiceWorker()) {
            return response()->json(['message'=>'User is not a service worker'],422);
        }

        $serviceRequest->assigned_to = $worker->id;
        $serviceRequest->status = 'in_progress';
        $serviceRequest->save();

        return response()->json($serviceRequest->fresh()->load(['assignedWorker','resident']));
    }

    // Optionally: change status (can be used by worker or admin)
    public function changeStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $data = $request->validate([
            'status'=>['required','in:pending,in_progress,completed,cancelled']
        ]);

        $user = $request->user();

        if ($user->isServiceWorker() && $serviceRequest->assigned_to !== $user->id) {
            return response()->json(['message'=>'You are not assigned to this request'],403);
        }

        if ($user->isResident() && $serviceRequest->resident_id !== $user->id) {
            return response()->json(['message'=>'Forbidden'],403);
        }

        // Residents cannot set status (except maybe cancel) — enforce business rules:
        if ($user->isResident() && $data['status'] !== 'cancelled') {
            return response()->json(['message'=>'Residents can only cancel their request'],403);
        }

        $serviceRequest->status = $data['status'];
        $serviceRequest->save();

        return response()->json($serviceRequest->fresh()->load(['assignedWorker','resident']));
    }
}
