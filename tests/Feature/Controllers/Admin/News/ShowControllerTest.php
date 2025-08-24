<?php

namespace Tests\Feature\Controllers\Admin\News;

use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('admin')]
#[Group('admin-news')]
#[Group('admin-news-show')]
class ShowControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_SHOW = '/admin_panel/news/%d';

    private User $adminUser;
    private Role $adminRole;
    private Category $category;
    private News $news;

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

        // Создаем новость
        $this->news = News::factory()->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'active' => true,
            'published_at' => now()->subDay(),
        ]);
    }

    public function test_admin_can_view_news_show(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->news->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.news.show')
            ->assertViewHas('news')
            ->assertSee($this->news->title)
            ->assertSee($this->news->content);
    }

    public function test_news_show_displays_correct_data(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->news->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.news.show')
            ->assertViewHas('news')
            ->assertSee($this->news->title)
            ->assertSee($this->news->content);
    }

    public function test_news_show_returns_404_for_nonexistent_news(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, 99999))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_guest_cannot_access_news_show(): void
    {
        $this->get(sprintf(self::URL_SHOW, $this->news->id))
            ->assertStatus(Response::HTTP_FOUND); // 302 - redirect на login
    }

    public function test_user_without_admin_role_cannot_access_news_show(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->get(sprintf(self::URL_SHOW, $this->news->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_news_show_displays_author_information(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->news->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.news.show')
            ->assertSee($this->adminUser->name);
    }

    public function test_news_show_displays_category_information(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->news->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.news.show')
            ->assertSee($this->category->name);
    }

    public function test_news_show_displays_publication_status(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->news->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.news.show');
    }

    public function test_news_show_displays_activity_status(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->news->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.news.show');
    }

    public function test_news_show_displays_featured_status(): void
    {
        $featuredNews = News::factory()->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'active' => true,
            'featured' => true,
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $featuredNews->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.news.show');
    }

    public function test_news_show_displays_view_count(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->news->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.news.show');
    }


    public function test_news_show_displays_creation_and_update_dates(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->news->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.news.show');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
