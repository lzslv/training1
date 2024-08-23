<?php

namespace App\Services;

use App\Models\Otp;
use App\Models\User;
use App\Notifications\SendOtpNotification;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    public function send(User $user)
    {

        $otp = rand(100000, 999999);

        Otp::create([
            'user_id' => $user->id,
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $user->notify(new SendOtpNotification($otp));
    }

    public function verify(User $user, $otp)
    {
        $otp = Otp::where('otp', $otp)
            ->where('user_id', $user->id)
            ->where('otp_expires_at', '>=', now())
            ->first();

        if (!$otp) {
            return ['status' => 'error', 'message' => 'Invalid or expired OTP'];
        }

        $otp->delete();

        return ['status' => 'success'];
    }
}
