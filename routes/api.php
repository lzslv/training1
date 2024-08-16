<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ChatController;
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
        Route::controller(UserController::class)->group(function () {
            Route::get('users', 'index');
            Route::get('users/{id}', 'show');
            Route::post('users', 'store');
            Route::put('users/{id}', 'update');
            Route::delete('users/{id}', 'destroy');
        });

        Route::controller(RoleController::class)->group(function () {
            Route::get('roles', 'index');
            Route::get('roles/{id}', 'show');
            Route::post('roles', 'store');
            Route::put('roles/{id}', 'update');
            Route::delete('roles/{id}', 'destroy');
        });
    });

    Route::controller(ChatController::class)->group(function () {
        Route::get('/chats', 'index');
        Route::post('/chats', 'store');
        Route::post('/chats/{chat}', 'sendMessage');
    });
});


