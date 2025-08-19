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
#[Group('admin-comments-destroy')]
class DestroyControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_DELETE = '/admin_panel/comments/%d';

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

    public function test_admin_can_delete_comment(): void
    {
        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $this->comment->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        $this->assertDatabaseMissing('comments', ['id' => $this->comment->id]);
    }

    public function test_delete_approved_comment(): void
    {
        $approvedComment = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'text' => 'Одобренный комментарий',
            'status' => 'approved',
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $approvedComment->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        $this->assertDatabaseMissing('comments', ['id' => $approvedComment->id]);
    }

    public function test_delete_pending_comment(): void
    {
        $pendingComment = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'text' => 'Ожидающий модерации комментарий',
            'status' => 'pending',
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $pendingComment->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        $this->assertDatabaseMissing('comments', ['id' => $pendingComment->id]);
    }

    public function test_delete_rejected_comment(): void
    {
        $rejectedComment = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'text' => 'Отклоненный комментарий',
            'status' => 'rejected',
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $rejectedComment->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        $this->assertDatabaseMissing('comments', ['id' => $rejectedComment->id]);
    }

    public function test_delete_comment_with_child_comments(): void
    {
        $parentComment = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'text' => 'Родительский комментарий',
            'status' => 'approved',
        ]);

        $childComments = Comment::factory(3)->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'parent_id' => $parentComment->id,
            'text' => 'Дочерний комментарий',
            'status' => 'approved',
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $parentComment->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        $this->assertDatabaseMissing('comments', ['id' => $parentComment->id]);
        
        // Проверяем, что дочерние комментарии остались, но parent_id стал null
        foreach ($childComments as $childComment) {
            $this->assertDatabaseHas('comments', ['id' => $childComment->id]);
            $this->assertDatabaseHas('comments', [
                'id' => $childComment->id,
                'parent_id' => null
            ]);
        }
    }

    public function test_delete_child_comment(): void
    {
        $parentComment = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'text' => 'Родительский комментарий',
            'status' => 'approved',
        ]);

        $childComment = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'parent_id' => $parentComment->id,
            'text' => 'Дочерний комментарий',
            'status' => 'approved',
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $childComment->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        $this->assertDatabaseMissing('comments', ['id' => $childComment->id]);
        // Родительский комментарий должен остаться
        $this->assertDatabaseHas('comments', ['id' => $parentComment->id]);
    }

    public function test_delete_comment_with_long_text(): void
    {
        $longComment = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'text' => str_repeat('Очень длинный комментарий. ', 100),
            'status' => 'approved',
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $longComment->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        $this->assertDatabaseMissing('comments', ['id' => $longComment->id]);
    }

    public function test_delete_comment_with_special_characters(): void
    {
        $specialComment = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'text' => 'Комментарий с символами: @#$%^&*()<>&"\'',
            'status' => 'approved',
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $specialComment->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        $this->assertDatabaseMissing('comments', ['id' => $specialComment->id]);
    }

    public function test_delete_comment_from_different_author(): void
    {
        $differentUser = User::factory()->create();
        
        $otherComment = Comment::factory()->create([
            'author_id' => $differentUser->id,
            'news_id' => $this->news->id,
            'text' => 'Комментарий от другого пользователя',
            'status' => 'approved',
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $otherComment->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        $this->assertDatabaseMissing('comments', ['id' => $otherComment->id]);
    }

    public function test_delete_comment_on_inactive_news(): void
    {
        $inactiveNews = News::factory()->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'active' => false,
            'published_at' => now()->subDay(),
        ]);

        $commentOnInactiveNews = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $inactiveNews->id,
            'text' => 'Комментарий к неактивной новости',
            'status' => 'approved',
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $commentOnInactiveNews->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        $this->assertDatabaseMissing('comments', ['id' => $commentOnInactiveNews->id]);
    }

    public function test_delete_old_comment(): void
    {
        $oldComment = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'text' => 'Старый комментарий',
            'status' => 'approved',
            'created_at' => now()->subMonths(6),
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $oldComment->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        $this->assertDatabaseMissing('comments', ['id' => $oldComment->id]);
    }

    public function test_delete_recent_comment(): void
    {
        $recentComment = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'text' => 'Свежий комментарий',
            'status' => 'pending',
            'created_at' => now()->subMinutes(5),
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $recentComment->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        $this->assertDatabaseMissing('comments', ['id' => $recentComment->id]);
    }

    public function test_delete_comment_with_html_content(): void
    {
        $htmlComment = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'text' => 'Комментарий с <b>HTML</b> тегами и <script>alert("test")</script>',
            'status' => 'approved',
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $htmlComment->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        $this->assertDatabaseMissing('comments', ['id' => $htmlComment->id]);
    }

    public function test_delete_returns_404_for_nonexistent_comment(): void
    {
        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, 99999))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_guest_cannot_delete_comment(): void
    {
        $this->delete(sprintf(self::URL_DELETE, $this->comment->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/login');

        $this->assertDatabaseHas('comments', ['id' => $this->comment->id]);
    }

    public function test_user_without_admin_role_cannot_delete_comment(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->delete(sprintf(self::URL_DELETE, $this->comment->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertDatabaseHas('comments', ['id' => $this->comment->id]);
    }

    public function test_delete_comment_with_deeply_nested_replies(): void
    {
        // Создаем цепочку вложенных комментариев
        $level1 = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'text' => 'Комментарий 1 уровня',
            'status' => 'approved',
        ]);

        $level2 = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'parent_id' => $level1->id,
            'text' => 'Комментарий 2 уровня',
            'status' => 'approved',
        ]);

        $level3 = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'parent_id' => $level2->id,
            'text' => 'Комментарий 3 уровня',
            'status' => 'approved',
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $level1->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        // Проверяем, что родительский комментарий удалился
        $this->assertDatabaseMissing('comments', ['id' => $level1->id]);
        // Дочерние комментарии могут остаться с тем же parent_id, если каскадное удаление не настроено
        $this->assertDatabaseHas('comments', ['id' => $level2->id]);
        $this->assertDatabaseHas('comments', ['id' => $level3->id]);
    }

    public function test_delete_comment_success_message(): void
    {
        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $this->comment->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments')
            ->assertSessionHas('success');
    }

    public function test_multiple_comments_deletion(): void
    {
        $comment1 = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'text' => 'Комментарий 1',
            'status' => 'approved',
        ]);

        $comment2 = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'text' => 'Комментарий 2',
            'status' => 'pending',
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $comment1->id))
            ->assertStatus(Response::HTTP_FOUND);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $comment2->id))
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseMissing('comments', ['id' => $comment1->id]);
        $this->assertDatabaseMissing('comments', ['id' => $comment2->id]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
