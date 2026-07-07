<?php
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;

// Public routes for user registration and login
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth')->name('register');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth')->name('login');

// Logout route, protected by Sanctum authentication middleware
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    // Verify email (the link from the email hits this)
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return response()->json(['message' => 'Email verified successfully']);
    })->middleware(['signed'])->name('verification.verify');

    // Resend verification email
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Verification link sent']);
    })->middleware(['throttle:6,1']);
});

// Protected routes for tasks and categories, requiring authentication, email verification, and rate limiting
Route::middleware(['auth:sanctum', 'verified', 'throttle:api'])->group(function () {

    // Get all notifications for the authenticated user
    Route::get('/notifications', function (Request $request) {
        return response()->json($request->user()->notifications);
    });

    Route::post('/notifications/{id}/read', function (Request $request, $id) {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['message' => 'Marked as read']);
    });

    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::post('/tasks/{id}/restore', [TaskController::class, 'restore'])->name('tasks.restore');
    Route::post('/categories/{id}/restore', [CategoryController::class, 'restore'])->name('categories.restore');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
