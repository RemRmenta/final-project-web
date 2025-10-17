<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        // admin only
        $users = User::select('id','name','email','role','profile_photo','created_at')->paginate(25);
        return response()->json($users);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'=>'sometimes|string|max:255',
            'email'=>['sometimes','email',Rule::unique('users')->ignore($user->id)],
            'password'=>'sometimes|string|min:6|confirmed',
            'role'=>['sometimes', Rule::in(['resident','service_worker','admin'])],
            'profile_photo'=>'sometimes|file|image|max:4096'
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) Storage::disk('public')->delete($user->profile_photo);
            $data['profile_photo'] = $request->file('profile_photo')->store('profiles','public');
        }

        if (isset($data['password'])) $data['password'] = Hash::make($data['password']);
        $user->update($data);

        return response()->json($user);
    }

    public function destroy(User $user)
    {
        if($user->profile_photo) Storage::disk('public')->delete($user->profile_photo);
        $user->tokens()->delete();
        $user->delete();
        return response()->json(['message'=>'User deleted']);
    }
}
