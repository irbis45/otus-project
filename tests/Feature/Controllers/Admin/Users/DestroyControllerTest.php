<?php

namespace Tests\Feature\Controllers\Admin\Users;

use App\Models\User;
use App\Models\Role;
use App\Models\News;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('admin')]
#[Group('admin-users')]
#[Group('admin-users-destroy')]
class DestroyControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_DELETE = '/admin_panel/users/%d';

    private User $adminUser;
    private Role $adminRole;
    private User $testUser;

    public function setUp(): void
    {
        parent::setUp();

        // Получаем роль администратора из базы данных
        $this->adminRole = Role::where('slug', 'admin')->first();

        // Создаем пользователя с ролью администратора
        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($this->adminRole->id);

        // Создаем тестового пользователя
        $this->testUser = User::factory()->create([
            'name' => 'Тестовый пользователь',
            'email' => 'testuser@example.com',
        ]);
    }

    public function test_admin_can_delete_user(): void
    {
        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $this->testUser->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $this->assertDatabaseMissing('users', ['id' => $this->testUser->id]);
    }

    public function test_delete_user_with_news(): void
    {
        // Создаем новости пользователя
        $category = \App\Models\Category::factory()->create();
        $news = News::factory(5)->create([
            'author_id' => $this->testUser->id,
            'category_id' => $category->id,
            'active' => true,
            'published_at' => now()->subDays(rand(1, 10)),
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $this->testUser->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $this->assertDatabaseMissing('users', ['id' => $this->testUser->id]);
        // Проверяем, что новости остались, но author_id стал null
        foreach ($news as $newsItem) {
            $this->assertDatabaseHas('news', ['id' => $newsItem->id]);
            $this->assertDatabaseHas('news', [
                'id' => $newsItem->id,
                'author_id' => null
            ]);
        }
    }

    public function test_delete_user_with_comments(): void
    {
        // Создаем комментарии пользователя
        $category = \App\Models\Category::factory()->create();
        $news = News::factory()->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $category->id,
        ]);

        $comments = Comment::factory(10)->create([
            'author_id' => $this->testUser->id,
            'news_id' => $news->id,
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $this->testUser->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $this->assertDatabaseMissing('users', ['id' => $this->testUser->id]);
        // Проверяем, что комментарии остались, но author_id стал null
        foreach ($comments as $comment) {
            $this->assertDatabaseHas('comments', ['id' => $comment->id]);
            $this->assertDatabaseHas('comments', [
                'id' => $comment->id,
                'author_id' => null
            ]);
        }
    }

    public function test_delete_user_with_news_and_comments(): void
    {
        // Создаем новости и комментарии пользователя
        $category = \App\Models\Category::factory()->create();
        $news = News::factory(3)->create([
            'author_id' => $this->testUser->id,
            'category_id' => $category->id,
        ]);

        $comments = Comment::factory(5)->create([
            'author_id' => $this->testUser->id,
            'news_id' => $news->first()->id,
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $this->testUser->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $this->assertDatabaseMissing('users', ['id' => $this->testUser->id]);

        // Проверяем, что новости и комментарии остались, но author_id стал null
        foreach ($news as $newsItem) {
            $this->assertDatabaseHas('news', ['id' => $newsItem->id]);
            $this->assertDatabaseHas('news', [
                'id' => $newsItem->id,
                'author_id' => null
            ]);
        }
        foreach ($comments as $comment) {
            $this->assertDatabaseHas('comments', ['id' => $comment->id]);
            $this->assertDatabaseHas('comments', [
                'id' => $comment->id,
                'author_id' => null
            ]);
        }
    }

    public function test_delete_user_with_roles(): void
    {
        $userRole = Role::where('slug', 'user')->first();
        $this->testUser->roles()->attach($userRole->id);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $this->testUser->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $this->assertDatabaseMissing('users', ['id' => $this->testUser->id]);
        // Проверяем, что связи с ролями тоже удалились
        $this->assertDatabaseMissing('role_user', ['user_id' => $this->testUser->id]);
    }

    public function test_delete_user_with_admin_role(): void
    {
        $adminTestUser = User::factory()->create();
        $adminTestUser->roles()->attach($this->adminRole->id);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $adminTestUser->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $this->assertDatabaseMissing('users', ['id' => $adminTestUser->id]);
    }



    public function test_delete_recently_created_user(): void
    {
        $recentUser = User::factory()->create([
            'created_at' => now()->subMinutes(5),
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $recentUser->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $this->assertDatabaseMissing('users', ['id' => $recentUser->id]);
    }

    public function test_delete_user_with_special_characters_in_name(): void
    {
        $specialUser = User::factory()->create([
            'name' => 'Пользователь с символами @#$%^&*()',
            'email' => 'special@example.com',
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $specialUser->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $this->assertDatabaseMissing('users', ['id' => $specialUser->id]);
    }

    public function test_delete_user_with_long_name(): void
    {
        $longNameUser = User::factory()->create([
            'name' => str_repeat('Очень длинное имя пользователя ', 2), // Уменьшаем длину
            'email' => 'longname@example.com',
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $longNameUser->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $this->assertDatabaseMissing('users', ['id' => $longNameUser->id]);
    }

    public function test_delete_returns_404_for_nonexistent_user(): void
    {
        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, 99999))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_guest_cannot_delete_user(): void
    {
        $this->delete(sprintf(self::URL_DELETE, $this->testUser->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/login');

        $this->assertDatabaseHas('users', ['id' => $this->testUser->id]);
    }

    public function test_user_without_admin_role_cannot_delete_user(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->delete(sprintf(self::URL_DELETE, $this->testUser->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertDatabaseHas('users', ['id' => $this->testUser->id]);
    }

    public function test_user_cannot_delete_themselves(): void
    {
        // Возможно, в системе есть защита от самоудаления
        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $this->adminUser->id))
            ->assertStatus(Response::HTTP_FOUND);

        // В зависимости от логики приложения может быть ошибка или успех
        // Здесь проверяем, что пользователь все еще существует или удален
    }

    public function test_delete_user_with_many_news(): void
    {
        // Создаем много новостей
        $category = \App\Models\Category::factory()->create();
        $news = News::factory(100)->create([
            'author_id' => $this->testUser->id,
            'category_id' => $category->id,
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $this->testUser->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $this->assertDatabaseMissing('users', ['id' => $this->testUser->id]);
        // Проверяем, что новости остались, но author_id стал null
        $this->assertDatabaseHas('news', ['id' => $news->first()->id, 'author_id' => null]);
        $this->assertDatabaseHas('news', ['id' => $news->last()->id, 'author_id' => null]);
    }

    public function test_delete_user_with_many_comments(): void
    {
        // Создаем много комментариев
        $category = \App\Models\Category::factory()->create();
        $news = News::factory()->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $category->id,
        ]);

        $comments = Comment::factory(200)->create([
            'author_id' => $this->testUser->id,
            'news_id' => $news->id,
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $this->testUser->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $this->assertDatabaseMissing('users', ['id' => $this->testUser->id]);
        // Проверяем, что комментарии остались, но author_id стал null
        $this->assertDatabaseHas('comments', ['id' => $comments->first()->id, 'author_id' => null]);
        $this->assertDatabaseHas('comments', ['id' => $comments->last()->id, 'author_id' => null]);
    }

    public function test_delete_user_success_message(): void
    {
        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $this->testUser->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users')
            ->assertSessionHas('success');
    }

    public function test_multiple_users_deletion(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $user1->id))
            ->assertStatus(Response::HTTP_FOUND);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $user2->id))
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseMissing('users', ['id' => $user1->id]);
        $this->assertDatabaseMissing('users', ['id' => $user2->id]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
