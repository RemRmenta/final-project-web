<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ServiceRequestController;
use App\Http\Controllers\API\CategoryController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Sanctum Authenticated)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Authenticated user logout
    Route::post('logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | User Management (Admin Only for Index/Delete)
    |--------------------------------------------------------------------------
    */
    Route::get('users', [UserController::class, 'index'])->middleware('role:admin');
    Route::get('users/{user}', [UserController::class, 'show'])->middleware('role:admin,service_worker,resident');
    Route::put('users/{user}', [UserController::class, 'update'])->middleware('role:admin,service_worker,resident');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('role:admin');

    /*
    |--------------------------------------------------------------------------
    | Service Request Routes (Role-Based)
    |--------------------------------------------------------------------------
    */
    Route::get('requests', [ServiceRequestController::class, 'index']);
    Route::post('requests', [ServiceRequestController::class, 'store'])->middleware('role:resident');
    Route::get('requests/{serviceRequest}', [ServiceRequestController::class, 'show']);
    Route::put('requests/{serviceRequest}', [ServiceRequestController::class, 'update']);
    Route::delete('requests/{serviceRequest}', [ServiceRequestController::class, 'destroy']);

    // Admin: assign request to worker
    Route::post('requests/{serviceRequest}/assign', [ServiceRequestController::class, 'assign'])->middleware('role:admin');

    // Worker/Admin: change request status (resident may cancel)
    Route::post('requests/{serviceRequest}/status', [ServiceRequestController::class, 'changeStatus']);

    /*
    |--------------------------------------------------------------------------
    | Category Routes (Admin Only)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {
        Route::get('categories', [CategoryController::class, 'index']);
        Route::post('categories', [CategoryController::class, 'store']);
        Route::get('categories/{category}', [CategoryController::class, 'show']);
        Route::put('categories/{category}', [CategoryController::class, 'update']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
    });
});

/*
|--------------------------------------------------------------------------
| Authenticated User Route
|--------------------------------------------------------------------------
*/
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
