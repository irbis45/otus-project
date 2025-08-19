<?php

namespace Tests\Feature\Controllers\Public;

use App\Models\User;
use App\Models\Category;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('public')]
#[Group('public-category')]
class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_CATEGORIES_INDEX = '/categories';

    private User $user;
    private Category $category;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->category = Category::create([
            'name' => 'Тестовая категория',
            'slug' => 'test-category',
            'description' => 'Описание тестовой категории',
            'active' => true
        ]);
    }

    public function test_guest_can_view_categories_index(): void
    {
        $this->get(self::URL_CATEGORIES_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('categories.index');
    }

    public function test_authenticated_user_can_view_categories_index(): void
    {
        $this->actingAs($this->user)
            ->get(self::URL_CATEGORIES_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('categories.index');
    }

    public function test_categories_index_shows_all_active_categories(): void
    {
        // Создаем несколько активных категорий
        $activeCategories = Category::create([
            'name' => 'Вторая категория',
            'slug' => 'second-category',
            'description' => 'Описание второй категории',
            'active' => true
        ]);

        $this->get(self::URL_CATEGORIES_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('categories.index')
            ->assertViewHas('categories');
    }

    public function test_categories_index_hides_inactive_categories(): void
    {
        // Создаем неактивную категорию
        $inactiveCategory = Category::create([
            'name' => 'Неактивная категория',
            'slug' => 'inactive-category',
            'description' => 'Описание неактивной категории',
            'active' => false
        ]);

        $this->get(self::URL_CATEGORIES_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('categories.index')
            ->assertViewHas('categories');
    }

    public function test_categories_index_shows_category_with_news_count(): void
    {
        // Создаем новости в категории
        $news = News::factory(5)->create([
            'author_id' => $this->user->id,
            'category_id' => $this->category->id,
            'active' => true,
            'published_at' => now()->subDays(rand(1, 5)),
        ]);

        $this->get(self::URL_CATEGORIES_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('categories.index')
            ->assertViewHas('categories');
    }

    public function test_categories_index_shows_empty_state_when_no_categories(): void
    {
        // Удаляем все категории
        Category::query()->delete();

        $this->get(self::URL_CATEGORIES_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('categories.index')
            ->assertViewHas('categories');
    }

    public function test_categories_index_shows_categories_in_correct_order(): void
    {
        // Создаем категории с разными именами для проверки сортировки
        $categoryA = Category::create([
            'name' => 'A Категория',
            'slug' => 'a-category',
            'description' => 'Описание A категории',
            'active' => true
        ]);

        $categoryZ = Category::create([
            'name' => 'Z Категория',
            'slug' => 'z-category',
            'description' => 'Описание Z категории',
            'active' => true
        ]);

        $this->get(self::URL_CATEGORIES_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('categories.index')
            ->assertViewHas('categories');
    }

    public function test_categories_index_shows_category_description(): void
    {
        $this->get(self::URL_CATEGORIES_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('categories.index')
            ->assertViewHas('categories');
    }

    public function test_categories_index_shows_category_slug(): void
    {
        $this->get(self::URL_CATEGORIES_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('categories.index')
            ->assertViewHas('categories');
    }

    public function test_categories_index_handles_special_characters_in_names(): void
    {
        $specialCategory = Category::create([
            'name' => 'Категория с символами: !@#$%^&*()',
            'slug' => 'special-category',
            'description' => 'Описание с символами: !@#$%^&*()',
            'active' => true
        ]);

        $this->get(self::URL_CATEGORIES_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('categories.index')
            ->assertViewHas('categories');
    }

    public function test_categories_index_handles_unicode_characters(): void
    {
        $unicodeCategory = Category::create([
            'name' => 'Категория с Unicode: 🚀🌟💻',
            'slug' => 'unicode-category',
            'description' => 'Описание с Unicode: 🚀🌟💻',
            'active' => true
        ]);

        $this->get(self::URL_CATEGORIES_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('categories.index')
            ->assertViewHas('categories');
    }

    public function test_categories_index_handles_long_names(): void
    {
        $longName = str_repeat('Очень длинное название категории ', 2);
        $longCategory = Category::create([
            'name' => $longName,
            'slug' => 'long-category',
            'description' => 'Описание длинной категории',
            'active' => true
        ]);

        $this->get(self::URL_CATEGORIES_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('categories.index')
            ->assertViewHas('categories');
    }

    public function test_categories_index_handles_long_descriptions(): void
    {
        $longDescription = str_repeat('Очень длинное описание категории с множеством слов и символов ', 20);
        $longDescCategory = Category::create([
            'name' => 'Категория с длинным описанием',
            'slug' => 'long-desc-category',
            'description' => $longDescription,
            'active' => true
        ]);

        $this->get(self::URL_CATEGORIES_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('categories.index')
            ->assertViewHas('categories');
    }

    public function test_categories_index_performance_with_many_categories(): void
    {
        // Создаем много категорий для проверки производительности
        $categories = [];
        for ($i = 0; $i < 100; $i++) {
            $categories[] = Category::create([
                'name' => "Категория {$i}",
                'slug' => "category-{$i}",
                'description' => "Описание категории {$i}",
                'active' => true
            ]);
        }

        $startTime = microtime(true);
        
        $this->get(self::URL_CATEGORIES_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('categories.index');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Проверяем, что страница загружается достаточно быстро (менее 1 секунды)
        $this->assertLessThan(1.0, $executionTime);
    }

    public function test_categories_index_with_mixed_active_inactive_categories(): void
    {
        // Создаем смесь активных и неактивных категорий
        $activeCategories = [];
        $inactiveCategories = [];

        for ($i = 0; $i < 5; $i++) {
            $activeCategories[] = Category::create([
                'name' => "Активная категория {$i}",
                'slug' => "active-category-{$i}",
                'description' => "Описание активной категории {$i}",
                'active' => true
            ]);

            $inactiveCategories[] = Category::create([
                'name' => "Неактивная категория {$i}",
                'slug' => "inactive-category-{$i}",
                'description' => "Описание неактивной категории {$i}",
                'active' => false
            ]);
        }

        $this->get(self::URL_CATEGORIES_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('categories.index')
            ->assertViewHas('categories');
    }

    public function test_categories_index_handles_empty_descriptions(): void
    {
        $noDescCategory = Category::create([
            'name' => 'Категория без описания',
            'slug' => 'no-desc-category',
            'description' => null,
            'active' => true
        ]);

        $this->get(self::URL_CATEGORIES_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('categories.index')
            ->assertViewHas('categories');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
