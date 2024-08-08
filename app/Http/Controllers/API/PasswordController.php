<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\PasswordService;
use Illuminate\Http\Request;

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
