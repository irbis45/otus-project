<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\User;

class UserViewModel
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function id(): int
    {
        return $this->user->getId();
    }

    public function name(): string
    {
        return $this->user->getName();
    }

    public function email(): string
    {
        return $this->user->getEmail();
    }

    public function hasRole(string $role): bool
    {
        return $this->user->hasRole($role);
    }

    public function hasPermission(string $permission): bool
    {
        return $this->user->hasPermission($permission);
    }

    // Добавь другие метод, которые нужны в шаблоне
}

