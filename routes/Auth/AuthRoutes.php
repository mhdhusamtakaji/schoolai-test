<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;



// resirved for Admin
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::post('/register/teacher', [AuthController::class, 'register_teacher']);
});


// public Routes
Route::post('/register/student', [AuthController::class, 'register_student']);
// Route::match(['post', 'get'], '/login', [AuthController::class, 'login'])->name('login');
Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->name('verify_otp');
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Sanctum middleware is applied to protect these public routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
});

