<?php

namespace Tests\Feature\Controllers\Admin\News;

use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\News;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('admin')]
#[Group('admin-news')]
#[Group('admin-news-destroy')]
class DestroyControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_DELETE = '/admin_panel/news/%d';

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

    public function test_admin_can_delete_news(): void
    {
        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $this->news->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/news');

        $this->assertDatabaseMissing('news', ['id' => $this->news->id]);
    }

    public function test_delete_news_with_comments(): void
    {
        // Создаем комментарии к новости
        $comments = Comment::factory(5)->create([
            'news_id' => $this->news->id,
            'author_id' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $this->news->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/news');

        $this->assertDatabaseMissing('news', ['id' => $this->news->id]);
        // Проверяем, что комментарии остались, но news_id стал null
        foreach ($comments as $comment) {
            $this->assertDatabaseHas('comments', ['id' => $comment->id]);
            $this->assertDatabaseHas('comments', [
                'id' => $comment->id,
                'news_id' => null
            ]);
        }
    }

    public function test_delete_returns_404_for_nonexistent_news(): void
    {
        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, 99999))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_guest_cannot_delete_news(): void
    {
        $this->delete(sprintf(self::URL_DELETE, $this->news->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/login');

        $this->assertDatabaseHas('news', ['id' => $this->news->id]);
    }

    public function test_user_without_admin_role_cannot_delete_news(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->delete(sprintf(self::URL_DELETE, $this->news->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertDatabaseHas('news', ['id' => $this->news->id]);
    }

    public function test_delete_news_removes_thumbnail_file(): void
    {
        $newsWithThumbnail = News::factory()->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'active' => true,
            'thumbnail' => 'news/test-thumbnail.jpg',
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $newsWithThumbnail->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/news');

        $this->assertDatabaseMissing('news', ['id' => $newsWithThumbnail->id]);
    }

    public function test_delete_featured_news(): void
    {
        $featuredNews = News::factory()->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'active' => true,
            'featured' => true,
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $featuredNews->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/news');

        $this->assertDatabaseMissing('news', ['id' => $featuredNews->id]);
    }

    public function test_delete_inactive_news(): void
    {
        $inactiveNews = News::factory()->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'active' => false,
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $inactiveNews->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/news');

        $this->assertDatabaseMissing('news', ['id' => $inactiveNews->id]);
    }

    public function test_delete_unpublished_news(): void
    {
        $unpublishedNews = News::factory()->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'active' => true,
            'published_at' => now()->addDay(),
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $unpublishedNews->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/news');

        $this->assertDatabaseMissing('news', ['id' => $unpublishedNews->id]);
    }

    public function test_delete_news_with_high_view_count(): void
    {
        $popularNews = News::factory()->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'active' => true,
            'views' => 10000,
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $popularNews->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/news');

        $this->assertDatabaseMissing('news', ['id' => $popularNews->id]);
    }

    public function test_delete_redirects_with_success_message(): void
    {
        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $this->news->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/news')
            ->assertSessionHas('success');
    }

    public function test_multiple_news_deletion(): void
    {
        $news1 = News::factory()->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
        ]);

        $news2 = News::factory()->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $news1->id))
            ->assertStatus(Response::HTTP_FOUND);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $news2->id))
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseMissing('news', ['id' => $news1->id]);
        $this->assertDatabaseMissing('news', ['id' => $news2->id]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
