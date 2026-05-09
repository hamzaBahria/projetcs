<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SocialiteController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [\App\Http\Controllers\Api\ProfileController::class, 'health']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/forgot', [PasswordController::class, 'sendResetLink']);
Route::post('/password/reset', [PasswordController::class, 'reset']);
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->name('verification.verify');

Route::middleware('web')->group(function () {
    Route::get('/auth/google', [SocialiteController::class, 'redirectToGoogle']);
    Route::get('/auth/google/callback', [SocialiteController::class, 'handleGoogleCallback']);
    Route::post('/auth/google/set-password', [SocialiteController::class, 'setPassword']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [ProfileController::class, 'show']);
    Route::put('/user/update', [ProfileController::class, 'update']);
    Route::post('/user/avatar', [ProfileController::class, 'uploadAvatar']);
    Route::delete('/user', [ProfileController::class, 'destroy']);
    Route::put('/password/change', [PasswordController::class, 'change']);
});
