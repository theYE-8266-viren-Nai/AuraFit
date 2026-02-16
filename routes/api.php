<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\WorkoutPlanController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\AttendanceController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Member routes (Admin only for CRUD)
    Route::prefix('members')->group(function () {
        Route::get('/', [MemberController::class, 'index']); // Admin: View all members
        Route::post('/', [MemberController::class, 'store']); // Admin: Add member
        Route::get('/profile', [MemberController::class, 'profile']); // Member: View own profile
        Route::get('/{id}', [MemberController::class, 'show']); // Admin: View member details
        Route::put('/{id}', [MemberController::class, 'update']); // Admin: Update member
        Route::delete('/{id}', [MemberController::class, 'destroy']); // Admin: Delete member
    });

    // Membership routes
    Route::prefix('memberships')->group(function () {
        Route::get('/', [MembershipController::class, 'index']);
        Route::post('/', [MembershipController::class, 'store']);
        Route::get('/status', [MembershipController::class, 'membershipStatus']); // Member: View own membership status
        Route::get('/{id}', [MembershipController::class, 'show']);
        Route::put('/{id}', [MembershipController::class, 'update']);
        Route::delete('/{id}', [MembershipController::class, 'destroy']);
    });

    // Workout Plan routes
    Route::prefix('workout-plans')->group(function () {
        Route::get('/', [WorkoutPlanController::class, 'index']); // Trainer: View own plans
        Route::post('/', [WorkoutPlanController::class, 'store']); // Trainer: Create plan
        Route::get('/my-plans', [WorkoutPlanController::class, 'myWorkoutPlans']); // Member: View own plans
        Route::get('/{id}', [WorkoutPlanController::class, 'show']);
        Route::put('/{id}', [WorkoutPlanController::class, 'update']); // Trainer: Update plan
        Route::delete('/{id}', [WorkoutPlanController::class, 'destroy']);
    });

    // Payment routes
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']);
        Route::post('/', [PaymentController::class, 'store']); // Member: Make payment
        Route::get('/my-payments', [PaymentController::class, 'myPayments']); // Member: View own payments
        Route::get('/{id}', [PaymentController::class, 'show']);
        Route::get('/{id}/receipt', [PaymentController::class, 'generateReceipt']); // Generate receipt
    });

    // Attendance routes
    Route::prefix('attendance')->group(function () {
        Route::get('/', [AttendanceController::class, 'index']); // Admin/Trainer: View all
        Route::post('/', [AttendanceController::class, 'store']); // Admin: Mark attendance
        Route::post('/mark', [AttendanceController::class, 'markAttendance']); // Member: Mark own attendance
        Route::get('/my-attendance', [AttendanceController::class, 'myAttendance']); // Member: View own attendance
        Route::put('/{id}', [AttendanceController::class, 'update']);
    });
});