<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\PasswordService;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class PasswordController extends Controller
{
    protected $passwordService;
    public function __construct(PasswordService $passwordService)
    {
        $this->passwordService = $passwordService;
    }
    public function changeUserPassword(ChangePasswordRequest $request)
    {
        return $this->passwordService->changePassword($request->validated());
    }

    /**
     * @OA\Post(
     *     path="/api/auth/password/forgot",
     *     summary="Отправка ссылки для сброса пароля",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="vlad11@gmail.com")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешная отправка ссылки для сброса пароля",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Verification link sent to your email")
     *         )
     *     ),
     * )
     **/


    /**
     * @OA\Post(
     *     path="/api/auth/password/reset",
     *     summary="Сброс пароля",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="vlad11@gmail.com"),
     *             @OA\Property(property="token", type="string", example="7e044255d5599518268fcb6b026fc2668f5923e57063e6ee76b1f0e3312529d1"),
     *             @OA\Property(property="password", type="string", example="123123"),
     *             @OA\Property(property="password_confirmation", type="string", example="123123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Пароль успешно сброшен",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password reset successfully.")
     *         )
     *     ),
     * )
     **/

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        return $this->passwordService->sendResetLink($request->only('email'));
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        return $this->passwordService->resetPassword($request->validated());
    }
}
