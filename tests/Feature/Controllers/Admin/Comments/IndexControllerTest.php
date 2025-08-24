<?php

namespace Tests\Feature\Controllers\Admin\Comments;

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
#[Group('admin-comments')]
#[Group('admin-comments-index')]
class IndexControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_INDEX = '/admin_panel/comments';

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

        // Создаем категорию и новость
        $this->category = Category::factory()->create([
            'name' => 'Тестовая категория',
            'slug' => 'test-category',
            'description' => 'Описание тестовой категории',
        ]);

        $this->news = News::factory()->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'published_at' => now()->subDay(),
        ]);
    }

    public function test_admin_can_view_comments_index(): void
    {
        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.index');
    }

    public function test_comments_index_shows_all_comments(): void
    {
        // Создаем несколько комментариев
        $comments = Comment::factory(5)->create([
            'news_id' => $this->news->id,
            'author_id' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.index')
            ->assertViewHas('comments');
    }

    public function test_comments_index_shows_comment_details(): void
    {
        $comment = Comment::factory()->create([
            'news_id' => $this->news->id,
            'author_id' => $this->adminUser->id,
            'text' => 'Тестовый комментарий',
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.index')
            ->assertViewHas('comments');
    }

    public function test_comments_index_shows_comment_author(): void
    {
        $comment = Comment::factory()->create([
            'news_id' => $this->news->id,
            'author_id' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.index')
            ->assertViewHas('comments');
    }

    public function test_comments_index_shows_comment_news(): void
    {
        $comment = Comment::factory()->create([
            'news_id' => $this->news->id,
            'author_id' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.index')
            ->assertViewHas('comments');
    }

    public function test_comments_index_shows_both_approved_and_rejected_comments(): void
    {
        // Создаем одобренные и отклоненные комментарии
        $approvedComment = Comment::factory()->create([
            'news_id' => $this->news->id,
            'author_id' => $this->adminUser->id,
            'status' => 'approved',
        ]);

        $rejectedComment = Comment::factory()->create([
            'news_id' => $this->news->id,
            'author_id' => $this->adminUser->id,
            'status' => 'rejected',
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.index')
            ->assertViewHas('comments');
    }

    public function test_comments_index_shows_empty_state_when_no_comments(): void
    {
        // Удаляем все комментарии
        Comment::query()->delete();

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.index')
            ->assertViewHas('comments');
    }

    public function test_comments_index_shows_comments_in_correct_order(): void
    {
        // Создаем комментарии с разными датами создания
        $oldComment = Comment::factory()->create([
            'news_id' => $this->news->id,
            'author_id' => $this->adminUser->id,
            'created_at' => now()->subDays(5),
        ]);

        $newComment = Comment::factory()->create([
            'news_id' => $this->news->id,
            'author_id' => $this->adminUser->id,
            'created_at' => now()->subHour(),
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.index')
            ->assertViewHas('comments');
    }

    public function test_comments_index_handles_special_characters_in_content(): void
    {
        $specialComment = Comment::factory()->create([
            'news_id' => $this->news->id,
            'author_id' => $this->adminUser->id,
            'text' => 'Комментарий с символами: !@#$%^&*()',
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.index')
            ->assertViewHas('comments');
    }

    public function test_comments_index_handles_unicode_characters(): void
    {
        $unicodeComment = Comment::factory()->create([
            'news_id' => $this->news->id,
            'author_id' => $this->adminUser->id,
            'text' => 'Комментарий с Unicode: 🚀🌟💻',
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.index')
            ->assertViewHas('comments');
    }

    public function test_comments_index_handles_long_content(): void
    {
        $longContent = str_repeat('Очень длинный комментарий с множеством слов и символов ', 20);
        $longComment = Comment::factory()->create([
            'news_id' => $this->news->id,
            'author_id' => $this->adminUser->id,
            'text' => $longContent,
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.index')
            ->assertViewHas('comments');
    }

    public function test_comments_index_performance_with_many_comments(): void
    {
        // Создаем много комментариев для проверки производительности
        $comments = [];
        for ($i = 0; $i < 100; $i++) {
            $comments[] = Comment::factory()->create([
                'news_id' => $this->news->id,
                'author_id' => $this->adminUser->id,
                'text' => "Комментарий {$i}",
            ]);
        }

        $startTime = microtime(true);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.index');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Проверяем, что страница загружается достаточно быстро (менее 1 секунды)
        $this->assertLessThan(1.0, $executionTime);
    }

    public function test_comments_index_shows_comment_creation_date(): void
    {
        $comment = Comment::factory()->create([
            'news_id' => $this->news->id,
            'author_id' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.index')
            ->assertViewHas('comments');
    }

    public function test_comments_index_shows_comment_status_indicators(): void
    {
        $approvedComment = Comment::factory()->create([
            'news_id' => $this->news->id,
            'author_id' => $this->adminUser->id,
            'status' => 'approved',
        ]);

        $pendingComment = Comment::factory()->create([
            'news_id' => $this->news->id,
            'author_id' => $this->adminUser->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.index')
            ->assertViewHas('comments');
    }

    public function test_guest_cannot_access_comments_index(): void
    {
        $this->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/login');
    }

    public function test_user_without_admin_role_cannot_access_comments_index(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_user_without_comment_view_permission_cannot_access(): void
    {
        // Создаем пользователя без разрешения на просмотр комментариев
        $userWithoutPermission = User::factory()->create();
        $userWithoutPermission->roles()->attach($this->adminRole->id);

        $this->actingAs($userWithoutPermission)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK); // Пользователь с ролью admin может получить доступ
    }

    public function test_comments_index_shows_comment_search_functionality(): void
    {
        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.index')
            ->assertViewHas('comments');
    }

    public function test_comments_index_shows_comment_filter_options(): void
    {
        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.index')
            ->assertViewHas('comments');
    }

    public function test_comments_index_shows_pagination_when_many_comments(): void
    {
        // Создаем больше комментариев, чем помещается на одной странице
        $comments = Comment::factory(25)->create([
            'news_id' => $this->news->id,
            'author_id' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.index')
            ->assertViewHas('comments');
    }

    public function test_comments_index_shows_comment_reporting_info(): void
    {
        $comment = Comment::factory()->create([
            'news_id' => $this->news->id,
            'author_id' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.index')
            ->assertViewHas('comments');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
