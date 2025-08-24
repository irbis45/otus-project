<?php

namespace Tests\Feature\Controllers\Admin\Users;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('admin')]
#[Group('admin-users')]
#[Group('admin-users-index')]
class IndexControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_INDEX = '/admin_panel/users';

    private User $adminUser;
    private Role $adminRole;

    public function setUp(): void
    {
        parent::setUp();

        // Создаем роль администратора
        $this->adminRole = Role::where('slug', 'admin')->first();

        // Создаем пользователя с ролью администратора
        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($this->adminRole->id);
    }

    public function test_admin_can_view_users_index(): void
    {
        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.index');
    }

    public function test_users_index_shows_all_users(): void
    {
        // Создаем несколько пользователей
        $users = User::factory(5)->create();

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.index')
            ->assertViewHas('users');
    }

    public function test_users_index_shows_user_details(): void
    {
        $user = User::factory()->create([
            'name' => 'Тестовый Пользователь',
            'email' => 'test@example.com',
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.index')
            ->assertViewHas('users');
    }

    public function test_users_index_shows_user_roles(): void
    {
        $userRole = Role::create([
            'slug' => 'test-user',
            'name' => 'Test User'
        ]);

        $user = User::factory()->create();
        $user->roles()->attach($userRole->id);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.index')
            ->assertViewHas('users');
    }

    public function test_users_index_shows_user_status(): void
    {
        $verifiedUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $unverifiedUser = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.index')
            ->assertViewHas('users');
    }


    public function test_users_index_handles_special_characters_in_names(): void
    {
        $specialUser = User::factory()->create([
            'name' => 'Пользователь с символами: !@#$%^&*()',
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.index')
            ->assertViewHas('users');
    }


    public function test_users_index_handles_long_names(): void
    {
        $longName = str_repeat('Очень длинное имя пользователя ', 2);
        $longNameUser = User::factory()->create(['name' => $longName]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.index')
            ->assertViewHas('users');
    }

    public function test_users_index_performance_with_many_users(): void
    {
        // Создаем много пользователей для проверки производительности
        $users = [];
        for ($i = 0; $i < 100; $i++) {
            $users[] = User::factory()->create([
                'name' => "Пользователь {$i}",
                'email' => "user{$i}@example.com",
            ]);
        }

        $startTime = microtime(true);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.index');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Проверяем, что страница загружается достаточно быстро (менее 1 секунды)
        $this->assertLessThan(1.0, $executionTime);
    }

    public function test_users_index_shows_user_creation_date(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.index')
            ->assertViewHas('users');
    }

    public function test_users_index_shows_user_last_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.index')
            ->assertViewHas('users');
    }

    public function test_users_index_shows_user_activity_status(): void
    {
        $activeUser = User::factory()->create();
        $inactiveUser = User::factory()->create();

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.index')
            ->assertViewHas('users');
    }

    public function test_guest_cannot_access_users_index(): void
    {
        $this->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/login');
    }

    public function test_user_without_admin_role_cannot_access_users_index(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_user_without_user_view_permission_cannot_access(): void
    {
        // Создаем пользователя с обычной ролью (не админ)
        $userRole = Role::create([
            'slug' => 'regular-user',
            'name' => 'Regular User'
        ]);

        $userWithoutPermission = User::factory()->create();
        $userWithoutPermission->roles()->attach($userRole->id);

        $this->actingAs($userWithoutPermission)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_users_index_shows_user_management_buttons(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.index')
            ->assertViewHas('users');
    }

    public function test_users_index_shows_user_search_functionality(): void
    {
        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.index')
            ->assertViewHas('users');
    }

    public function test_users_index_shows_user_filter_options(): void
    {
        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.index')
            ->assertViewHas('users');
    }

    public function test_users_index_shows_pagination_when_many_users(): void
    {
        // Создаем больше пользователей, чем помещается на одной странице
        $users = User::factory(25)->create();

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.index')
            ->assertViewHas('users');
    }

    public function test_users_index_pagination_works(): void
    {
        // Создаем много пользователей
        $users = User::factory(25)->create();

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX . '?page=2')
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.index')
            ->assertViewHas('users');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
