<?php

namespace Tests\Feature\Controllers\Admin\News;

use App\Application\Core\News\UseCases\Queries\FetchAllPagination\Fetcher;
use App\Application\Core\News\UseCases\Queries\FetchAllPagination\Query;
use App\Application\Core\News\DTO\NewsDTO;
use App\Application\Core\News\DTO\AuthorDTO;
use App\Application\Core\News\DTO\CategoryDTO;
use App\Application\Core\News\DTO\PaginatedResult;
use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('admin')]
#[Group('admin-news')]
#[Group('admin-news-index')]
class IndexControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_INDEX = '/admin_panel/news';

    private User $adminUser;
    private Role $adminRole;
    private Category $category;

    public function setUp(): void
    {
        parent::setUp();

        // Получаем роль администратора из базы данных
        $this->adminRole = Role::where('slug', 'admin')->first();

        // Создаем пользователя с ролью администратора
        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($this->adminRole->id);

        // Создаем категорию
        $this->category = Category::create([
            'name' => 'Тестовая категория',
            'slug' => 'test-category',
            'description' => 'Описание тестовой категории',
            'active' => true
        ]);
    }

    public function test_admin_can_view_news_index(): void
    {
        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.news.index');
    }

    public function test_news_index_shows_paginated_news(): void
    {
        // Создаем 15 новостей
        News::factory()->count(15)->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'active' => true,
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.news.index')
            ->assertViewHas('news');
    }

    public function test_news_index_pagination_works(): void
    {
        // Создаем 25 новостей для проверки пагинации
        News::factory()->count(25)->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'active' => true,
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX . '?page=2')
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.news.index')
            ->assertViewHas('news');
    }

    public function test_guest_cannot_access_news_index(): void
    {
        $this->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_FOUND); // 302 - redirect на login
    }

    public function test_user_without_news_view_permission_cannot_access(): void
    {
        $userWithoutPermission = User::factory()->create();
        // Не привязываем роль admin

        $this->actingAs($userWithoutPermission)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_FORBIDDEN); // 403 - forbidden
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
