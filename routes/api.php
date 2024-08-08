<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PasswordController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/


Route::get('auth/google/redirect', [AuthController::class, 'googleRedirect']);
Route::get('auth/google/callback', [AuthController::class, 'googleCallback']);

Route::get('auth/facebook/redirect', [AuthController::class, 'facebookRedirect']);
Route::get('auth/facebook/callback', [AuthController::class, 'facebookCallback']);


Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/email/verify', [AuthController::class, 'verifyUserEmail']);
Route::post('auth/email/resend-verification', [AuthController::class, 'resendEmailVerificationLink']);
Route::post('password/forgot', [PasswordController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [PasswordController::class, 'resetPassword'])->name('password.reset');


Route::middleware(['auth:api'])->group(function () {
    Route::get('user', [AuthController::class, 'user']);
    Route::post('password/change', [PasswordController::class, 'changeUserPassword']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);

    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::get('users', [UserController::class, 'index']);
        Route::get('users/{id}', [UserController::class, 'show']);
        Route::post('users', [UserController::class, 'store']);
        Route::put('users/{id}', [UserController::class, 'update']);
        Route::delete('users/{id}', [UserController::class, 'destroy']);

        Route::get('roles', [RoleController::class, 'index']);
        Route::get('roles/{id}', [RoleController::class, 'show']);
        Route::post('roles', [RoleController::class, 'store']);
        Route::put('roles/{id}', [RoleController::class, 'update']);
        Route::delete('roles/{id}', [RoleController::class, 'destroy']);
    });

});


