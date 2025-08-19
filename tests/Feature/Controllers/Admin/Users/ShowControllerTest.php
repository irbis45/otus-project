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
#[Group('admin-users-show')]
class ShowControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_SHOW = '/admin_panel/users/%d';

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

    public function test_admin_can_view_user_show(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->testUser->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.show')
            ->assertViewHas('user')
            ->assertSee($this->testUser->name)
            ->assertSee($this->testUser->email);
    }

    public function test_user_show_displays_correct_data(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->testUser->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.show')
            ->assertViewHas('user')
            ->assertSee($this->testUser->name)
            ->assertSee($this->testUser->email);
    }

    public function test_user_show_returns_404_for_nonexistent_user(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, 99999))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_guest_cannot_access_user_show(): void
    {
        $this->get(sprintf(self::URL_SHOW, $this->testUser->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/login');
    }

    public function test_user_without_admin_role_cannot_access_user_show(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->get(sprintf(self::URL_SHOW, $this->testUser->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_user_show_displays_user_roles(): void
    {
        $userRole = Role::where('slug', 'user')->first();
        $this->testUser->roles()->attach($userRole->id);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->testUser->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.show');
    }

    public function test_user_show_displays_user_news_count(): void
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
            ->get(sprintf(self::URL_SHOW, $this->testUser->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.show');
    }

    public function test_user_show_displays_user_comments_count(): void
    {
        // Создаем комментарии пользователя
        $category = \App\Models\Category::factory()->create();
        $news = News::factory()->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $category->id,
        ]);
        
        $comments = Comment::factory(3)->create([
            'author_id' => $this->testUser->id,
            'news_id' => $news->id,
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->testUser->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.show');
    }

    public function test_user_show_displays_email_verification_status(): void
    {
        $this->testUser->email_verified_at = now();
        $this->testUser->save();

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->testUser->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.show');
    }

    public function test_user_show_displays_creation_date(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->testUser->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.show');
    }

    public function test_user_show_with_admin_role(): void
    {
        $adminTestUser = User::factory()->create();
        $adminTestUser->roles()->attach($this->adminRole->id);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $adminTestUser->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.show')
            ->assertViewHas('user')
            ->assertSee($adminTestUser->name)
            ->assertSee($adminTestUser->email);
    }

    public function test_user_show_with_multiple_roles(): void
    {
        $userRole = Role::where('slug', 'user')->first();
        $editorRole = Role::where('slug', 'editor')->first();
        
        $this->testUser->roles()->attach([$userRole->id, $editorRole->id]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->testUser->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.show');
    }

    public function test_user_show_with_long_name(): void
    {
        $longNameUser = User::factory()->create([
            'name' => str_repeat('Очень длинное имя пользователя ', 2),
            'email' => 'longname@example.com',
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $longNameUser->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.show')
            ->assertViewHas('user')
            ->assertSee($longNameUser->name)
            ->assertSee($longNameUser->email);
    }

    public function test_user_show_with_special_characters_in_name(): void
    {
        $specialUser = User::factory()->create([
            'name' => 'Пользователь с символами @#$%^&*()',
            'email' => 'special@example.com',
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $specialUser->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.show')
            ->assertViewHas('user')
            ->assertSee($specialUser->name)
            ->assertSee($specialUser->email);
    }

    public function test_user_show_with_unverified_email(): void
    {
        $unverifiedUser = User::factory()->create([
            'name' => 'Неверифицированный пользователь',
            'email' => 'unverified@example.com',
            'email_verified_at' => null,
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $unverifiedUser->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.show')
            ->assertViewHas('user')
            ->assertSee($unverifiedUser->name)
            ->assertSee($unverifiedUser->email);
    }

    public function test_user_show_with_many_news(): void
    {
        // Создаем много новостей
        $category = \App\Models\Category::factory()->create();
        $news = News::factory(25)->create([
            'author_id' => $this->testUser->id,
            'category_id' => $category->id,
            'active' => true,
            'published_at' => now()->subDays(rand(1, 30)),
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->testUser->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.show');
    }

    public function test_user_show_with_many_comments(): void
    {
        // Создаем много комментариев
        $category = \App\Models\Category::factory()->create();
        $news = News::factory()->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $category->id,
        ]);
        
        $comments = Comment::factory(50)->create([
            'author_id' => $this->testUser->id,
            'news_id' => $news->id,
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->testUser->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.show');
    }

    public function test_user_show_with_old_account(): void
    {
        $oldUser = User::factory()->create([
            'name' => 'Старый пользователь',
            'email' => 'old@example.com',
            'created_at' => now()->subYears(5),
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $oldUser->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.show')
            ->assertViewHas('user')
            ->assertSee($oldUser->name)
            ->assertSee($oldUser->email);
    }

    public function test_user_show_with_recent_account(): void
    {
        $recentUser = User::factory()->create([
            'name' => 'Новый пользователь',
            'email' => 'new@example.com',
            'created_at' => now()->subHours(1),
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $recentUser->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.show')
            ->assertViewHas('user')
            ->assertSee($recentUser->name)
            ->assertSee($recentUser->email);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
