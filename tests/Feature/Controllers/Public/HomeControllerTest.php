<?php

namespace Tests\Feature\Controllers\Public;


use App\Models\User;
use App\Models\Category;
use App\Models\News;
use Illuminate\Http\Response;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('public')]
#[Group('public-home')]
class HomeControllerTest extends PublicTestCase
{
    protected const URL_HOME = '/';

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

        $this->news = News::factory()->create([
            'author_id' => $this->user->id,
            'category_id' => $this->category->id,
            'active' => true,
            'published_at' => now()->subDay(),
        ]);
    }

    public function test_guest_can_view_home_page(): void
    {
        $this->get(self::URL_HOME)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('home');
    }

    public function test_home_page_shows_latest_news(): void
    {
        // Создаем несколько новостей
        $latestNews = News::factory(10)->create([
            'author_id' => $this->user->id,
            'category_id' => $this->category->id,
            'active' => true,
            'published_at' => now()->subDays(rand(1, 10)),
        ]);

        $this->get(self::URL_HOME)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('home')
            ->assertViewHas('latestNews')
            ->assertViewHas('featuredNews');
    }


    public function test_home_page_shows_featured_news(): void
    {
        // Создаем избранные новости
        $featuredNews = News::factory(5)->create([
            'author_id' => $this->user->id,
            'category_id' => $this->category->id,
            'active' => true,
            'featured' => true,
            'published_at' => now()->subDays(rand(1, 5)),
        ]);

        $this->get(self::URL_HOME)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('home')
            ->assertViewHas('featuredNews');
    }

    public function test_home_page_only_shows_active_news(): void
    {
        // Создаем активные и неактивные новости
        $activeNews = News::factory(5)->create([
            'author_id' => $this->user->id,
            'category_id' => $this->category->id,
            'active' => true,
            'published_at' => now()->subDays(rand(1, 5)),
        ]);

        $inactiveNews = News::factory(5)->create([
            'author_id' => $this->user->id,
            'category_id' => $this->category->id,
            'active' => false,
            'published_at' => now()->subDays(rand(1, 5)),
        ]);

        $this->get(self::URL_HOME)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('home')
            ->assertViewHas('latestNews');
    }

    public function test_home_page_only_shows_published_news(): void
    {
        // Создаем опубликованные и неопубликованные новости
        $publishedNews = News::factory(5)->create([
            'author_id' => $this->user->id,
            'category_id' => $this->category->id,
            'active' => true,
            'published_at' => now()->subDays(rand(1, 5)),
        ]);

        $unpublishedNews = News::factory(3)->create([
            'author_id' => $this->user->id,
            'category_id' => $this->category->id,
            'active' => true,
            'published_at' => now()->addDays(rand(1, 5)),
        ]);

        $this->get(self::URL_HOME)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('home')
            ->assertViewHas('latestNews');
    }

    public function test_home_page_defaults_to_page_one(): void
    {
        $this->get(self::URL_HOME)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('home')
            ->assertViewHas('latestNews');
    }

    public function test_home_page_handles_invalid_page_parameter(): void
    {
        $this->get(self::URL_HOME . '?page=invalid')
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/')
            ->assertSessionHasErrors(['page']);
    }

    public function test_home_page_handles_negative_page_parameter(): void
    {
        $this->get(self::URL_HOME . '?page=-1')
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/')
            ->assertSessionHasErrors(['page']);
    }

    public function test_home_page_handles_zero_page_parameter(): void
    {
        $this->get(self::URL_HOME . '?page=0')
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/')
            ->assertSessionHasErrors(['page']);
    }

    public function test_home_page_with_no_news(): void
    {
        // Удаляем все новости
        News::query()->delete();

        $this->get(self::URL_HOME)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('home')
            ->assertViewHas('latestNews')
            ->assertViewHas('featuredNews');
    }

    public function test_home_page_with_no_featured_news(): void
    {
        // Создаем только обычные новости
        $regularNews = News::factory(5)->create([
            'author_id' => $this->user->id,
            'category_id' => $this->category->id,
            'active' => true,
            'featured' => false,
            'published_at' => now()->subDays(rand(1, 5)),
        ]);

        $this->get(self::URL_HOME)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('home')
            ->assertViewHas('latestNews')
            ->assertViewHas('featuredNews');
    }

    public function test_home_page_query_string_preserved(): void
    {
        $this->get(self::URL_HOME . '?search=test&category=news')
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('home')
            ->assertViewHas('latestNews');
    }

    protected function tearDown(): void
    {
        Mockery::close();



        parent::tearDown();
    }


}
