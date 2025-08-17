<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $editor = User::where('email', 'editor@example.com')->first();

        $adminRole = Role::where('slug', 'admin')->first();
        $editorRole = Role::where('slug', 'editor')->first();
        $userRole = Role::where('slug', 'user')->first();

        if ($admin && $adminRole) {
            // Админ получает только роль 'admin'
            $admin->roles()->syncWithoutDetaching([$adminRole->id]);
        }

        if ($editor && $editorRole) {
            // Редактор получает только роль 'editor'
            $editor->roles()->syncWithoutDetaching([$editorRole->id]);
        }

        // Всем остальным пользователям назначаем роль 'user'
        $otherUsers = User::whereNotIn('email', ['admin@example.com', 'editor@example.com'])->get();

        foreach ($otherUsers as $user) {
            if ($userRole) {
                $user->roles()->syncWithoutDetaching([$userRole->id]);
            }
        }
    }
}


