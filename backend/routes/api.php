<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SocialiteController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/forgot', [PasswordController::class, 'sendResetLink']);
Route::post('/password/reset', [PasswordController::class, 'reset']);
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->name('verification.verify');
Route::get('/auth/google', [SocialiteController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [SocialiteController::class, 'handleGoogleCallback']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [ProfileController::class, 'show']);
    Route::put('/user/update', [ProfileController::class, 'update']);
    Route::post('/user/avatar', [ProfileController::class, 'uploadAvatar']);
    Route::put('/password/change', [PasswordController::class, 'change']);
});
