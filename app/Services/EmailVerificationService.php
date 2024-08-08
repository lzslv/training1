<?php

namespace App\Services;

use App\Models\EmailVerificationToken;
use App\Models\User;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmailVerificationService
{
    public function verifyEmail(string $email, string $token)
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return ['status' => 'failed', 'message' => 'User not found'];
        }
        $this->checkIfEmailIsVerified($user);
        $verifiedToken = $this->verifyToken($email, $token);
        if ($user->markEmailAsVerified()) {
            $verifiedToken->delete();
            return ['status' => 'success', 'message' => 'Email verified successfully'];
        } else {
            return ['status' => 'failed', 'message' => 'Email verification failed, try again later'];
        }

    }

    public function checkIfEmailIsVerified(User $user)
    {
        if ($user->email_verified_at) {
            response()->json([
                'status' => 'failed',
                'message' => 'Email already verified'
            ])->send();

            exit();
        }
    }

    public function verifyToken(string $email, string $token)
    {
        $token = EmailVerificationToken::where('email', $email)->where('token', $token)->first();
        if ($token) {
            if ($token->expired_at >= now()) {
                return $token;
            } else {
                $token->delete();
                response()->json([
                    'status' => 'failed',
                    'message' => 'Token expired'
                ])->send();
                exit();
            }
        } else {
            response()->json([
                'status' => 'failed',
                'message' => 'Token not provided'
            ])->send();
            exit();
        }
    }

    public function generateVerificationLink(string $email)
    {
        // если есть, то ее нахуй
        $checkIfTokenExists = EmailVerificationToken::where('email', $email)->first();
        if ($checkIfTokenExists) {
            $checkIfTokenExists->delete();
        }
        $token = Str::uuid();
        $url = config('app.url') . "?token=" . $token . "&email=" . $email;
        $saveToken = EmailVerificationToken::create([
            'email' => $email,
            'token' => $token,
            'expired_at' => now()->addMinutes(10)
        ]);
        // если создан, то збс, отправляем
        if ($saveToken) {
            return $url;
        }
    }

    public function sendVerificationLink(User $user)
    {
        // создает и отправляет сообщение, состоящее из самого юзера и ссылки
        Notification::send($user, new EmailVerificationNotification($this->generateVerificationLink($user->email)));
    }

    public function resendVerificationLink($email)
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            $this->sendVerificationLink($user);
            return ['status' => 'success', 'message' => 'Verification link sent to your email'];
        } else {
            return ['status' => 'failed', 'message' => 'User not found'];
        }
    }
}
