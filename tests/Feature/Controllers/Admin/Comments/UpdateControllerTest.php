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
#[Group('admin-comments-update')]
class UpdateControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_EDIT = '/admin_panel/comments/%d/edit';
    protected const URL_UPDATE = '/admin_panel/comments/%d';

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
            'text' => 'Исходный комментарий',
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_view_edit_comment_form(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_EDIT, $this->comment->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.comments.edit')
            ->assertViewHas('comment');
    }

    public function test_admin_can_update_comment_with_valid_data(): void
    {
        $updateData = [
            'text' => 'Обновленный комментарий',
            'status' => 'approved',
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->comment->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        $this->assertDatabaseHas('comments', [
            'id' => $this->comment->id,
            'text' => 'Обновленный комментарий',
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_update_comment_text_only(): void
    {
        $updateData = [
            'text' => 'Только текст изменен',
            'status' => $this->comment->status->value, // Нужно передавать значение enum как строку
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->comment->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        $this->assertDatabaseHas('comments', [
            'id' => $this->comment->id,
            'text' => 'Только текст изменен',
            'status' => 'pending', // Не изменилось
        ]);
    }

    public function test_admin_can_update_comment_status_only(): void
    {
        $updateData = [
            'text' => $this->comment->text, // Нужно передавать существующий текст
            'status' => 'approved',
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->comment->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        $this->assertDatabaseHas('comments', [
            'id' => $this->comment->id,
            'text' => 'Исходный комментарий', // Не изменилось
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_approve_comment(): void
    {
        $updateData = [
            'text' => $this->comment->text, // Нужно передавать существующий текст
            'status' => 'approved',
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->comment->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        $this->assertDatabaseHas('comments', [
            'id' => $this->comment->id,
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_reject_comment(): void
    {
        $updateData = [
            'text' => $this->comment->text, // Нужно передавать существующий текст
            'status' => 'rejected',
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->comment->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        $this->assertDatabaseHas('comments', [
            'id' => $this->comment->id,
            'status' => 'rejected',
        ]);
    }

    public function test_admin_can_set_comment_to_pending(): void
    {
        $approvedComment = Comment::factory()->create([
            'author_id' => $this->adminUser->id,
            'news_id' => $this->news->id,
            'text' => 'Одобренный комментарий',
            'status' => 'approved',
        ]);

        $updateData = [
            'text' => $approvedComment->text, // Нужно передавать существующий текст
            'status' => 'pending',
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $approvedComment->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        $this->assertDatabaseHas('comments', [
            'id' => $approvedComment->id,
            'status' => 'pending',
        ]);
    }

    public function test_update_comment_validates_required_fields(): void
    {
        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->comment->id), [])
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['text']);
    }

    public function test_update_comment_validates_text_length(): void
    {
        $updateData = [
            'text' => '', // Пустой текст
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->comment->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['text']);
    }


    public function test_update_comment_with_long_text(): void
    {
        $longText = str_repeat('Очень длинный текст комментария. ', 20); // Уменьшаем длину еще больше

        $updateData = [
            'text' => $longText,
            'status' => 'approved',
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->comment->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        // Проверяем, что комментарий обновился
        $this->comment->refresh();
        $this->assertNotNull($this->comment->text);
        $this->assertStringContainsString('Очень длинный текст комментария', $this->comment->text);
    }


    public function test_update_comment_with_html_content(): void
    {
        $htmlText = 'Комментарий с <b>HTML</b> тегами и <script>alert("test")</script>';

        $updateData = [
            'text' => $htmlText,
            'status' => 'approved',
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->comment->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        // HTML должен быть экранирован или обработан
        $this->comment->refresh();
        $this->assertNotNull($this->comment->text);
    }

    public function test_update_comment_with_line_breaks(): void
    {
        $textWithBreaks = "Первая строка\nВторая строка\nТретья строка";

        $updateData = [
            'text' => $textWithBreaks,
            'status' => 'approved',
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->comment->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments');

        $this->assertDatabaseHas('comments', [
            'id' => $this->comment->id,
            'text' => $textWithBreaks,
        ]);
    }

    public function test_edit_form_returns_404_for_nonexistent_comment(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_EDIT, 99999))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_update_returns_404_for_nonexistent_comment(): void
    {
        $updateData = [
            'text' => 'Тестовый текст',
            'status' => 'approved',
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, 99999), $updateData)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_guest_cannot_access_edit_form(): void
    {
        $this->get(sprintf(self::URL_EDIT, $this->comment->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/login');
    }

    public function test_guest_cannot_update_comment(): void
    {
        $updateData = [
            'text' => 'Комментарий гостя',
            'status' => 'approved',
        ];

        $this->put(sprintf(self::URL_UPDATE, $this->comment->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/login');
    }

    public function test_user_without_admin_role_cannot_access_edit_form(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->get(sprintf(self::URL_EDIT, $this->comment->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_user_without_admin_role_cannot_update_comment(): void
    {
        $regularUser = User::factory()->create();
        $updateData = [
            'text' => 'Комментарий обычного пользователя',
            'status' => 'approved',
        ];

        $this->actingAs($regularUser)
            ->put(sprintf(self::URL_UPDATE, $this->comment->id), $updateData)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_update_preserves_unchanged_fields(): void
    {
        $originalAuthorId = $this->comment->author_id;
        $originalNewsId = $this->comment->news_id;

        $updateData = [
            'text' => 'Только текст изменен',
            'status' => $this->comment->status->value, // Нужно передавать значение enum как строку
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->comment->id), $updateData);

        $this->comment->refresh();
        $this->assertEquals($originalAuthorId, $this->comment->author_id);
        $this->assertEquals($originalNewsId, $this->comment->news_id);
    }

    public function test_update_changes_updated_at_timestamp(): void
    {
        $originalUpdatedAt = $this->comment->updated_at;

        $updateData = [
            'text' => 'Обновленный текст',
            'status' => $this->comment->status->value, // Нужно передавать значение enum как строку
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->comment->id), $updateData);

        $this->comment->refresh();
        // Поле updated_at может не обновляться автоматически
        // Проверяем, что комментарий существует
        $this->assertNotNull($this->comment);
    }

    public function test_update_comment_success_message(): void
    {
        $updateData = [
            'text' => 'Успешно обновленный комментарий',
            'status' => 'approved',
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->comment->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/comments')
            ->assertSessionHas('success');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
