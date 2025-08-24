<?php

namespace Tests\Feature\Controllers\Public;

use App\Models\User;
use App\Models\Category;
use App\Models\News;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('public')]
#[Group('public-news')]
class NewsControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_NEWS_SHOW = '/news/%s';
    protected const URL_NEWS_CATEGORY = '/category/%s';
    protected const URL_NEWS_SEARCH = '/search';

    private User $user;
    private Category $category;
    private News $news;

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
            'slug' => 'test-news-slug',
        ]);
    }

    public function test_guest_can_view_news_show(): void
    {
        $this->get(sprintf(self::URL_NEWS_SHOW, $this->news->slug))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('news.show')
            ->assertViewHas('news');
    }

    public function test_authenticated_user_can_view_news_show(): void
    {
        $this->actingAs($this->user)
            ->get(sprintf(self::URL_NEWS_SHOW, $this->news->slug))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('news.show')
            ->assertViewHas('news');
    }

    public function test_news_show_returns_404_for_invalid_slug(): void
    {
        $this->get(sprintf(self::URL_NEWS_SHOW, 'invalid-slug'))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_news_show_returns_404_for_inactive_news(): void
    {
        $inactiveNews = News::factory()->create([
            'author_id' => $this->user->id,
            'category_id' => $this->category->id,
            'active' => false,
            'published_at' => now()->subDay(),
            'slug' => 'inactive-news',
        ]);

        $this->get(sprintf(self::URL_NEWS_SHOW, $inactiveNews->slug))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_news_show_returns_404_for_unpublished_news(): void
    {
        $unpublishedNews = News::factory()->create([
            'author_id' => $this->user->id,
            'category_id' => $this->category->id,
            'active' => true,
            'published_at' => now()->addDay(),
            'slug' => 'unpublished-news',
        ]);

        $this->get(sprintf(self::URL_NEWS_SHOW, $unpublishedNews->slug))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_news_show_increments_view_count(): void
    {
        $initialViews = $this->news->views;

        $this->get(sprintf(self::URL_NEWS_SHOW, $this->news->slug));

        $this->news->refresh();
        $this->assertEquals($initialViews + 1, $this->news->views);
    }

    public function test_news_show_shows_comments(): void
    {
        $comments = Comment::factory(5)->create([
            'news_id' => $this->news->id,
            'author_id' => $this->user->id,
        ]);

        $this->get(sprintf(self::URL_NEWS_SHOW, $this->news->slug))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('news.show')
            ->assertViewHas('news');
    }

    public function test_guest_can_view_news_by_category(): void
    {
        $this->get(sprintf(self::URL_NEWS_CATEGORY, $this->category->slug))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('news.by_category');
    }

    public function test_authenticated_user_can_view_news_by_category(): void
    {
        $this->actingAs($this->user)
            ->get(sprintf(self::URL_NEWS_CATEGORY, $this->category->slug))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('news.by_category');
    }

    public function test_news_by_category_returns_404_for_invalid_category(): void
    {
        $this->get(sprintf(self::URL_NEWS_CATEGORY, 'invalid-category'))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }


    public function test_news_by_category_shows_only_news_from_category(): void
    {
        // Создаем новости в другой категории
        $otherCategory = Category::create([
            'name' => 'Другая категория',
            'slug' => 'other-category',
            'description' => 'Описание другой категории',
            'active' => true
        ]);

        $otherNews = News::factory(3)->create([
            'author_id' => $this->user->id,
            'category_id' => $otherCategory->id,
            'active' => true,
            'published_at' => now()->subDays(rand(1, 5)),
        ]);

        $this->get(sprintf(self::URL_NEWS_CATEGORY, $this->category->slug))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('news.by_category');
    }

    public function test_news_by_category_pagination_works(): void
    {
        // Создаем много новостей в категории
        $news = News::factory(15)->create([
            'author_id' => $this->user->id,
            'category_id' => $this->category->id,
            'active' => true,
            'published_at' => now()->subDays(rand(1, 15)),
        ]);

        $this->get(sprintf(self::URL_NEWS_CATEGORY, $this->category->slug) . '?page=2')
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('news.by_category');
    }

    public function test_guest_can_search_news(): void
    {
        $this->get(self::URL_NEWS_SEARCH . '?query=test')
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('news.search');
    }


    public function test_news_search_requires_query_parameter(): void
    {
        $this->get(self::URL_NEWS_SEARCH)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/');
    }


    public function test_news_search_finds_matching_news(): void
    {
        $matchingNews = News::factory()->create([
            'author_id' => $this->user->id,
            'category_id' => $this->category->id,
            'title' => 'Новость с уникальным словом',
            'content' => 'Содержание новости',
            'active' => true,
            'published_at' => now()->subDay(),
        ]);

        $this->get(self::URL_NEWS_SEARCH . '?query=уникальным')
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('news.search');
    }

    public function test_news_search_only_shows_active_published_news(): void
    {
        // Создаем активные и неактивные новости
        $activeNews = News::factory(5)->create([
            'author_id' => $this->user->id,
            'category_id' => $this->category->id,
            'title' => 'Активная новость для поиска',
            'active' => true,
            'published_at' => now()->subDay(),
        ]);

        $inactiveNews = News::factory(3)->create([
            'author_id' => $this->user->id,
            'category_id' => $this->category->id,
            'title' => 'Неактивная новость для поиска',
            'active' => false,
            'published_at' => now()->subDay(),
        ]);

        $this->get(self::URL_NEWS_SEARCH . '?query=поиска')
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('news.search');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
