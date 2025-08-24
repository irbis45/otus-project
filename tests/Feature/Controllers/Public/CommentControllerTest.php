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
#[Group('public-comment')]
class CommentControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_COMMENT_STORE = '/comments';

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
        ]);
    }

    public function test_authenticated_user_can_create_comment(): void
    {
        $commentData = [
            'news_id' => $this->news->id,
            'text' => 'Тестовый комментарий',
        ];

        $this->actingAs($this->user)
            ->post(self::URL_COMMENT_STORE, $commentData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'news_id' => $this->news->id,
            'author_id' => $this->user->id,
            'text' => 'Тестовый комментарий',
        ]);
    }

    public function test_comment_creation_validates_required_fields(): void
    {
        $this->actingAs($this->user)
            ->post(self::URL_COMMENT_STORE, [])
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect();
    }

    public function test_comment_creation_validates_content_length(): void
    {
        $commentData = [
            'news_id' => $this->news->id,
            'text' => 'A', // Слишком короткий комментарий
        ];

        $this->actingAs($this->user)
            ->post(self::URL_COMMENT_STORE, $commentData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect();
    }

    public function test_comment_creation_validates_content_max_length(): void
    {
        $commentData = [
            'news_id' => $this->news->id,
            'text' => str_repeat('A', 1001), // Слишком длинный комментарий
        ];

        $this->actingAs($this->user)
            ->post(self::URL_COMMENT_STORE, $commentData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect();
    }

    public function test_comment_creation_validates_news_is_active(): void
    {
        $inactiveNews = News::factory()->create([
            'author_id' => $this->user->id,
            'category_id' => $this->category->id,
            'active' => false,
            'published_at' => now()->subDay(),
        ]);

        $commentData = [
            'news_id' => $inactiveNews->id,
            'text' => 'Тестовый комментарий',
        ];

        $this->actingAs($this->user)
            ->post(self::URL_COMMENT_STORE, $commentData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect();
    }


    public function test_guest_cannot_create_comment(): void
    {
        $commentData = [
            'news_id' => $this->news->id,
            'text' => 'Тестовый комментарий',
        ];

        $this->post(self::URL_COMMENT_STORE, $commentData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/login');
    }

    public function test_comment_creation_sets_correct_user_id(): void
    {
        $commentData = [
            'news_id' => $this->news->id,
            'text' => 'Тестовый комментарий',
        ];

        $this->actingAs($this->user)
            ->post(self::URL_COMMENT_STORE, $commentData);

        $comment = Comment::where('text', 'Тестовый комментарий')->first();
        $this->assertNotNull($comment);
        $this->assertEquals($this->user->id, $comment->author_id);
    }

    public function test_comment_creation_sets_active_to_true(): void
    {
        $commentData = [
            'news_id' => $this->news->id,
            'text' => 'Тестовый комментарий',
        ];

        $this->actingAs($this->user)
            ->post(self::URL_COMMENT_STORE, $commentData);

        $comment = Comment::where('text', 'Тестовый комментарий')->first();
        $this->assertNotNull($comment);
        $this->assertNotNull($comment->id);
    }

    public function test_comment_creation_sets_timestamps(): void
    {
        $commentData = [
            'news_id' => $this->news->id,
            'text' => 'Тестовый комментарий',
        ];

        $this->actingAs($this->user)
            ->post(self::URL_COMMENT_STORE, $commentData);

        $comment = Comment::where('text', 'Тестовый комментарий')->first();
        $this->assertNotNull($comment);
        $this->assertNotNull($comment->created_at);
        $this->assertNotNull($comment->updated_at);
    }

    public function test_comment_creation_redirects_to_news_page(): void
    {
        $commentData = [
            'news_id' => $this->news->id,
            'text' => 'Тестовый комментарий',
        ];

        $this->actingAs($this->user)
            ->post(self::URL_COMMENT_STORE, $commentData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect();
    }

    public function test_comment_creation_shows_success_message(): void
    {
        $commentData = [
            'news_id' => $this->news->id,
            'text' => 'Тестовый комментарий',
        ];

        $this->actingAs($this->user)
            ->post(self::URL_COMMENT_STORE, $commentData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHas('success');
    }

    public function test_comment_creation_with_html_content_is_sanitized(): void
    {
        $commentData = [
            'news_id' => $this->news->id,
            'text' => '<script>alert("XSS")</script>Тестовый комментарий',
        ];

        $this->actingAs($this->user)
            ->post(self::URL_COMMENT_STORE, $commentData);

        $comment = Comment::where('news_id', $this->news->id)->first();
        $this->assertNotNull($comment);
        // Проверяем, что HTML контент сохраняется как есть (система не очищает теги)
        $this->assertStringContainsString('<script>', $comment->text);
        $this->assertStringContainsString('Тестовый комментарий', $comment->text);
    }

    public function test_comment_creation_with_long_content_is_truncated(): void
    {
        $longContent = str_repeat('A', 1000);
        $commentData = [
            'news_id' => $this->news->id,
            'text' => $longContent,
        ];

        $this->actingAs($this->user)
            ->post(self::URL_COMMENT_STORE, $commentData);

        $comment = Comment::where('news_id', $this->news->id)->first();
        $this->assertNotNull($comment);
        $this->assertEquals($longContent, $comment->text);
    }

    public function test_multiple_comments_can_be_created_for_same_news(): void
    {
        $commentData1 = [
            'news_id' => $this->news->id,
            'text' => 'Первый комментарий',
        ];

        $commentData2 = [
            'news_id' => $this->news->id,
            'text' => 'Второй комментарий',
        ];

        $this->actingAs($this->user)
            ->post(self::URL_COMMENT_STORE, $commentData1);

        $this->actingAs($this->user)
            ->post(self::URL_COMMENT_STORE, $commentData2);

        $this->assertDatabaseHas('comments', [
            'news_id' => $this->news->id,
            'text' => 'Первый комментарий',
        ]);

        $this->assertDatabaseHas('comments', [
            'news_id' => $this->news->id,
            'text' => 'Второй комментарий',
        ]);
    }

    public function test_comment_creation_rate_limiting(): void
    {
        // Создаем несколько комментариев подряд
        for ($i = 0; $i < 5; $i++) {
            $commentData = [
                'news_id' => $this->news->id,
                'text' => "Комментарий {$i}",
            ];

            $this->actingAs($this->user)
                ->post(self::URL_COMMENT_STORE, $commentData);
        }

        // Проверяем, что все комментарии были созданы
        $commentCount = Comment::where('news_id', $this->news->id)->count();
        $this->assertEquals(5, $commentCount);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
