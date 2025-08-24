<?php

namespace Tests\Feature\Controllers\Admin\News;

use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('admin')]
#[Group('admin-news')]
#[Group('admin-news-update')]
class UpdateControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_EDIT = '/admin_panel/news/%d/edit';
    protected const URL_UPDATE = '/admin_panel/news/%d';

    private User $adminUser;
    private Role $adminRole;
    private Category $category;
    private News $news;

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

        // Создаем новость
        $this->news = News::factory()->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'active' => true,
            'published_at' => now()->subDay(),
            'title' => 'Исходный заголовок',
            'content' => 'Исходное содержание',
        ]);
    }

    public function test_admin_can_view_edit_news_form(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_EDIT, $this->news->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.news.edit')
            ->assertViewHas('news')
            ->assertSee($this->news->title)
            ->assertSee($this->news->content);
    }

    public function test_admin_can_update_news_with_valid_data(): void
    {
        $updateData = [
            'title' => 'Обновленный заголовок',
            'content' => 'Обновленное содержание',
            'excerpt' => 'Обновленное краткое описание',
            'category_id' => $this->category->id,
            'active' => false,
            'featured' => true,
            'published_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->news->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect(sprintf('/admin_panel/news/%d', $this->news->id));

        $this->assertDatabaseHas('news', [
            'id' => $this->news->id,
            'title' => 'Обновленный заголовок',
            'content' => 'Обновленное содержание',
            'active' => false,
            'featured' => true,
        ]);
    }

    public function test_admin_can_update_news_with_new_thumbnail(): void
    {
        $newThumbnail = UploadedFile::fake()->image('new-thumbnail.jpg', 800, 600);

        $updateData = [
            'title' => $this->news->title,
            'content' => $this->news->content,
            'category_id' => $this->category->id,
            'thumbnail' => $newThumbnail,
            'active' => true,
            'featured' => false,
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->news->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect(sprintf('/admin_panel/news/%d', $this->news->id));

        $this->news->refresh();
        $this->assertNotNull($this->news->thumbnail);
    }

    public function test_update_news_validates_required_fields(): void
    {
        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->news->id), [])
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['title', 'content', 'category_id']);
    }

    public function test_update_news_validates_title_length(): void
    {
        $updateData = [
            'title' => 'A', // Слишком короткий
            'content' => $this->news->content,
            'category_id' => $this->category->id,
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->news->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['title']);
    }

    public function test_update_news_validates_category_exists(): void
    {
        $updateData = [
            'title' => $this->news->title,
            'content' => $this->news->content,
            'category_id' => 99999, // Несуществующая категория
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->news->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['category_id']);
    }

    public function test_update_news_validates_thumbnail_type(): void
    {
        $invalidFile = UploadedFile::fake()->create('document.pdf', 100);

        $updateData = [
            'title' => $this->news->title,
            'content' => $this->news->content,
            'category_id' => $this->category->id,
            'thumbnail' => $invalidFile,
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->news->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['thumbnail']);
    }

    public function test_edit_form_returns_404_for_nonexistent_news(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_EDIT, 99999))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }


    public function test_guest_cannot_access_edit_form(): void
    {
        $this->get(sprintf(self::URL_EDIT, $this->news->id))
            ->assertStatus(Response::HTTP_FOUND); // 302 - redirect на login
    }

    public function test_guest_cannot_update_news(): void
    {
        $updateData = [
            'title' => 'Обновленный заголовок',
            'content' => 'Обновленное содержание',
            'category_id' => $this->category->id,
        ];

        $this->put(sprintf(self::URL_UPDATE, $this->news->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND); // 302 - redirect на login
    }

    public function test_user_without_admin_role_cannot_access_edit_form(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->get(sprintf(self::URL_EDIT, $this->news->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_user_without_admin_role_cannot_update_news(): void
    {
        $regularUser = User::factory()->create();
        $updateData = [
            'title' => 'Обновленный заголовок',
            'content' => 'Обновленное содержание',
            'category_id' => $this->category->id,
        ];

        $this->actingAs($regularUser)
            ->put(sprintf(self::URL_UPDATE, $this->news->id), $updateData)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_update_changes_updated_at_timestamp(): void
    {
        $originalUpdatedAt = $this->news->updated_at;

        // Добавляем небольшую задержку для изменения timestamp
        sleep(1);

        $updateData = [
            'title' => 'Новость для проверки timestamp',
            'content' => 'Содержание для проверки timestamp',
            'category_id' => $this->category->id,
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->news->id), $updateData);

        $this->news->refresh();
        $this->assertGreaterThan($originalUpdatedAt->timestamp, $this->news->updated_at->timestamp);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
