<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\News;
use App\Models\Comment;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    public function definition(): array
    {
        $user = User::inRandomOrder()->first() ?? User::factory()->create();
        $authorId = $user->id;

        $news = News::inRandomOrder()->first() ?? News::factory()->create();
        $newsId = $news->id;

        $parentId = null;
        // С вероятностью 50% пытаемся назначить родительский комментарий
        if ($this->faker->boolean(50)) {
            $parentComment = Comment::where('news_id', $newsId)
                                    ->whereNull('parent_id')
                                    ->inRandomOrder()
                                    ->first();

            if ($parentComment) {
                $parentId = $parentComment->id;
            }
        }

        return [
            'author_id' => $authorId,
            'parent_id' => $parentId,
            'news_id' => $newsId,
            'status' => $this->faker->randomElement(['approved', 'rejected', 'pending']),
            'text' => $this->faker->paragraph,
        ];
    }
}
