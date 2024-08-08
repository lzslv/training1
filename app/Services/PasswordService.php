<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordService
{
    public function validateCurrentPassword(string $current_password)
    {
        if (!password_verify($current_password, auth()->user()->password)) {
            response()->json([
                'status' => 'failed',
                'message' => 'Current password is wrong'
            ])->send();
            exit();
        }
    }

    public function changePassword(array $data)
    {
        $this->validateCurrentPassword($data['current_password']);
        $updatedPassword = auth()->user()->update([
            'password' => Hash::make($data['password'])
        ]);

        if ($updatedPassword) {
            return response()->json([
                'status' => 'success',
                'message' => 'Password has been updated'
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to update password'
            ]);
        }
    }

    public function sendResetLink(array $credentials)
    {
        $response = Password::sendResetLink($credentials);

        if ($response == Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Reset link sent to your email.'], 200);
        }

        return response()->json(['message' => 'Unable to send reset link'], 400);
    }

    public function resetPassword(array $credentials)
    {
        $response = Password::reset(
            $credentials,
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($response == Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password reset successfully.'], 200);
        }

        return response()->json(['message' => 'Failed to reset password.'], 400);
    }
}
