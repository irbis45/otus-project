<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Сначала создаем роли
        $this->call(RoleSeeder::class);

        // Создаем пользователей
        User::factory()->create([
                                             'name' => 'admin',
                                             'email' => 'admin@example.com',
                                         ]);

        User::factory()->create([
                                              'name' => 'editor',
                                              'email' => 'editor@example.com',
                                          ]);

        User::factory(5)->create();

        // Назначаем роли пользователям
        $this->call(UserRoleSeeder::class);

        // Последовательный вызов других сидеров
        $this->call([
                        NewsSeeder::class,
                        CommentSeeder::class,
                    ]);
    }
}
