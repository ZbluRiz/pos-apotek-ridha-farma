<?php

namespace App\Application\UseCases\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginUser
{
    public function execute(array $credentials): User
    {
        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password tidak sesuai.',
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
