<?php

declare(strict_types=1);

namespace App\Infrastructure\PasswordHasher;

use App\Application\Contracts\PasswordHasherInterface;
use Illuminate\Support\Facades\Hash;

class LaravelPasswordHasher implements PasswordHasherInterface
{
    /**
     * Хеширует пароль
     *
     * @param string $password
     * @return string
     */
    public function hash(string $password): string
    {
        return Hash::make($password);
    }

    /**
     * Проверяет соответствие пароля хешу
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function check(string $password, string $hash): bool
    {
        return Hash::check($password, $hash);
    }
}
