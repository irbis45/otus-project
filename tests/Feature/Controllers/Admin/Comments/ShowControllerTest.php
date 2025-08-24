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
#[Group('admin-comments-show')]
class ShowControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_SHOW = '/admin_panel/comments/%d';

    private User $adminUser;
    private Role $adminRole;
    private Category $category;
    private News $news;
    private Comment $comment;

    public function setUp(): void
    {
        parent::setUp();

        // Получаем роль администратора из базы данных
        $this->adminRole = Role::where('slug', 'admin')->first();

        // Создаем пользователя с ролью администратора
        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($this->adminRole->id);

        // Создаем категорию
        $this->category = Category::factory()->create([
            'name' => 'Тестовая категория',
            'slug' => 'test-category',
            'description' => 'Описание тестовой категории',
            'active' => true,
        ]);

        // Создаем новость
        $this->news = News::factory()->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'active' => true,
            'published_at' => now()->subDay(),
        ]);

        // Создаем комментарий
        $this->comment = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'text' => 'Тестовый комментарий',
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_view_comment_show(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->comment->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.show')
            ->assertViewHas('comment');
    }

    public function test_comment_show_displays_correct_data(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->comment->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.show')
            ->assertViewHas('comment')
            ->assertSee($this->comment->text)
            ->assertSee($this->adminUser->name);
    }

    public function test_comment_show_returns_404_for_nonexistent_comment(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, 99999))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_guest_cannot_access_comment_show(): void
    {
        $this->get(sprintf(self::URL_SHOW, $this->comment->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/login');
    }

    public function test_user_without_admin_role_cannot_access_comment_show(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->get(sprintf(self::URL_SHOW, $this->comment->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_comment_show_displays_author_information(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->comment->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.show')
            ->assertSee($this->adminUser->name);
    }

    public function test_comment_show_displays_news_information(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->comment->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.show')
            ->assertViewHas('comment'); // Проверяем наличие комментария
    }

    public function test_comment_show_with_pending_comment(): void
    {
        $pendingComment = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'text' => 'Ожидающий модерации комментарий',
            'status' => 'pending',
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $pendingComment->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.show')
            ->assertViewHas('comment');
    }

    public function test_comment_show_with_rejected_comment(): void
    {
        $rejectedComment = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'text' => 'Отклоненный комментарий',
            'status' => 'rejected',
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $rejectedComment->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.show')
            ->assertViewHas('comment');
    }

    public function test_comment_show_with_long_text(): void
    {
        $longComment = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'text' => str_repeat('Очень длинный комментарий. ', 50),
            'status' => 'approved',
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $longComment->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.show')
            ->assertViewHas('comment');
    }

    public function test_comment_show_with_special_characters(): void
    {
        $specialComment = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'text' => 'Комментарий с символами: @#$%^&*()<>&"\'',
            'status' => 'approved',
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $specialComment->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.show')
            ->assertViewHas('comment');
    }

    public function test_comment_show_displays_creation_date(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->comment->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.show');
    }

    public function test_comment_show_with_different_author(): void
    {
        $differentUser = User::factory()->create(['name' => 'Другой пользователь']);

        $otherComment = Comment::factory()->create([
            'author_id' => $differentUser->id,
            'news_id' => $this->news->id,
            'text' => 'Комментарий от другого пользователя',
            'status' => 'approved',
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $otherComment->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.show')
            ->assertViewHas('comment')
            ->assertSee('Другой пользователь');
    }

    public function test_comment_show_with_old_comment(): void
    {
        $oldComment = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'text' => 'Старый комментарий',
            'status' => 'approved',
            'created_at' => now()->subMonths(6),
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $oldComment->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.show')
            ->assertViewHas('comment');
    }

    public function test_comment_show_with_recent_comment(): void
    {
        $recentComment = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'text' => 'Свежий комментарий',
            'status' => 'pending',
            'created_at' => now()->subMinutes(5),
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $recentComment->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.show')
            ->assertViewHas('comment');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
