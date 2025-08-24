<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

class MenuBuilder
{
    public function build(array $menu, ?User $user): array
    {
        return array_filter($menu, function ($item) use ($user) {
            if (!isset($item['permission'])) {
                return true; // без проверки права
            }

            if ($user) {
                return $user->hasPermission($item['permission']);
            } else {
                return false;
            }
        });
    }
}
