<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResendEmailVerificationLinkRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\EmailVerificationService;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    protected $service;
    protected $emailVerificationService;

    public function __construct(AuthService $service, EmailVerificationService $emailVerificationService)
    {
        $this->service = $service;
        $this->emailVerificationService = $emailVerificationService;
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->service->register($request->validated());

        $this->emailVerificationService->sendVerificationLink($user);

        return new UserResource($user);
    }

    public function resendEmailVerificationLink(ResendEmailVerificationLinkRequest $request)
    {
        $response = $this->emailVerificationService->resendVerificationLink($request->email);
        return response()->json($response);
    }

    public function verifyUserEmail(VerifyEmailRequest $request)
    {
        return $this->emailVerificationService->verifyEmail($request->email, $request->token);
    }

    public function login(LoginRequest $request)
    {
        if (!$token = auth('api')->attempt($request->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->createNewToken($token);
    }

    public function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => new UserResource(auth('api')->user())
        ]);
    }

    public function user()
    {
        return response()->json(new UserResource(auth()->user()));
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

}
