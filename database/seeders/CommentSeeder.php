<?php

namespace Database\Seeders;

use App\Models\Comment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Создать 20 верхнеуровневых комментариев (без parent_id)
        Comment::factory(20)->create(['parent_id' => null]);

        // 2. Создать 10 комментариев с parent_id, выбирая случайного родителя из уже созданных комментариев
        Comment::factory(10)->create()->each(function ($comment) {
            $parent = Comment::whereNull('parent_id')->inRandomOrder()->first();
            if ($parent) {
                $comment->parent_id = $parent->id;
                $comment->save();
            }
        });
    }
}

