<?php

namespace Tests\Feature\Controllers\Admin\News;

use App\Application\Core\News\UseCases\Commands\Create\Command as CreateNewsCommand;
use App\Application\Core\News\UseCases\Commands\Create\Handler as CreateNewsHandler;
use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('admin')]
#[Group('admin-news')]
#[Group('admin-news-create')]
class CreateControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_CREATE = '/admin_panel/news/create';
    protected const URL_STORE = '/admin_panel/news';

    private User $adminUser;
    private Role $adminRole;
    private Category $category;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

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

    public function test_admin_can_view_create_news_form(): void
    {
        $this->actingAs($this->adminUser)
            ->get(self::URL_CREATE)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.news.create');
    }

    public function test_admin_can_create_news_with_valid_data(): void
    {
        $newsData = [
            'title' => 'Тестовая новость',
            'content' => 'Содержание тестовой новости',
            'excerpt' => 'Краткое описание',
            'category_id' => $this->category->id,
            'active' => true,
            'featured' => false,
            'published_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ];

        $this->actingAs($this->adminUser)
            ->post(self::URL_STORE, $newsData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/news');

        $this->assertDatabaseHas('news', [
            'title' => 'Тестовая новость',
            'content' => 'Содержание тестовой новости',
            'category_id' => $this->category->id,
            'author_id' => $this->adminUser->id,
        ]);
    }

    public function test_admin_can_create_news_with_thumbnail(): void
    {
        $thumbnail = UploadedFile::fake()->image('thumbnail.jpg', 800, 600);

        $newsData = [
            'title' => 'Новость с изображением',
            'content' => 'Содержание новости',
            'excerpt' => 'Краткое описание',
            'category_id' => $this->category->id,
            'thumbnail' => $thumbnail,
            'active' => true,
            'featured' => false,
        ];

        $this->actingAs($this->adminUser)
            ->post(self::URL_STORE, $newsData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/news');

        $this->assertDatabaseHas('news', [
            'title' => 'Новость с изображением',
            'content' => 'Содержание новости',
        ]);

        // Проверяем, что файл был загружен
        $news = News::where('title', 'Новость с изображением')->first();
        $this->assertNotNull($news->thumbnail);
    }

    public function test_create_news_validates_required_fields(): void
    {
        $this->actingAs($this->adminUser)
            ->post(self::URL_STORE, [])
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['title', 'content', 'category_id']);
    }

    public function test_create_news_validates_title_length(): void
    {
        $newsData = [
            'title' => 'A', // Слишком короткий
            'content' => 'Содержание новости',
            'category_id' => $this->category->id,
        ];

        $this->actingAs($this->adminUser)
            ->post(self::URL_STORE, $newsData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['title']);
    }

    public function test_create_news_validates_category_exists(): void
    {
        $newsData = [
            'title' => 'Тестовая новость',
            'content' => 'Содержание новости',
            'category_id' => 99999, // Несуществующая категория
        ];

        $this->actingAs($this->adminUser)
            ->post(self::URL_STORE, $newsData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['category_id']);
    }

    public function test_create_news_validates_thumbnail_type(): void
    {
        $invalidFile = UploadedFile::fake()->create('document.pdf', 100);

        $newsData = [
            'title' => 'Тестовая новость',
            'content' => 'Содержание новости',
            'category_id' => $this->category->id,
            'thumbnail' => $invalidFile,
        ];

        $this->actingAs($this->adminUser)
            ->post(self::URL_STORE, $newsData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['thumbnail']);
    }

    public function test_guest_cannot_access_create_form(): void
    {
        $this->get(self::URL_CREATE)
            ->assertStatus(Response::HTTP_FOUND); // 302 - redirect на login
    }

    public function test_guest_cannot_create_news(): void
    {
        $newsData = [
            'title' => 'Тестовая новость',
            'content' => 'Содержание тестовой новости',
            'category_id' => $this->category->id,
        ];

        $this->post(self::URL_STORE, $newsData)
            ->assertStatus(Response::HTTP_FOUND); // 302 - redirect на login
    }

    public function test_user_without_admin_role_cannot_access_create_form(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->get(self::URL_CREATE)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_user_without_admin_role_cannot_create_news(): void
    {
        $regularUser = User::factory()->create();
        $newsData = [
            'title' => 'Тестовая новость',
            'content' => 'Содержание новости',
            'category_id' => $this->category->id,
        ];

        $this->actingAs($regularUser)
            ->post(self::URL_STORE, $newsData)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
