<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\News>
 */
class NewsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::inRandomOrder()->first();
        $authorId = $user->id ?? User::factory()->create()->id;

        $category = Category::inRandomOrder()->first();
        $categoryId = $category->id ?? Category::factory()->create()->id;

        return [
            'author_id' => $authorId,
            'category_id' => $categoryId,
            'title' => fake()->sentence,
            'excerpt' => fake()->sentence,
            'content' => fake()->text,
            'published_at' => fake()->dateTimeBetween('-14 day', '+1 day'),
            'active' => fake()->boolean(70),
            'featured' => fake()->boolean(30),
            'views' => fake()->numberBetween(0, 1000),
            'thumbnail' => function () {
                if (fake()->boolean(30)) {
                    return null;
                } else {
                    // Создаём фейковый диск, если нужно (чтобы изолировать тестовые файлы)
                    Storage::fake('public');
                    // Создаём "фейковый" загруженный файл (реальный временный файл с картинкой)
                    $file = UploadedFile::fake()->image('thumbnail.jpg', 640, 480);
                    // Затем сохраняем этот файл в хранилище, как при обычном загрузке
                    return $file->store('news', 'public'); // путь относительно storage/app/public/news
                }
            },
        ];
    }
}
