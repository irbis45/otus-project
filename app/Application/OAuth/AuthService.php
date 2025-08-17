<?php

declare(strict_types=1);

namespace App\Application\OAuth;

use App\Application\OAuth\Contracts\AuthServiceInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class AuthService implements AuthServiceInterface
{
    public function attempt(string $email, string $password): ?Authenticatable
    {
        $credentials = compact('email', 'password');

        if (Auth::attempt($credentials)) {
            return Auth::user();
        }

        return null;
    }
}
