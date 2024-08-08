<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data) {

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        return $user;
    }
}
