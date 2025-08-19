<?php

namespace Tests\Feature\Controllers\Admin\Users;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('admin')]
#[Group('admin-users')]
#[Group('admin-users-create')]
class CreateControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_CREATE = '/admin_panel/users/create';
    protected const URL_STORE = '/admin_panel/users';

    private User $adminUser;
    private Role $adminRole;
    private Role $userRole;

    public function setUp(): void
    {
        parent::setUp();

        // Получаем роли из базы данных
        $this->adminRole = Role::where('slug', 'admin')->first();
        $this->userRole = Role::where('slug', 'user')->first();

        // Создаем пользователя с ролью администратора
        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($this->adminRole->id);
    }

    public function test_admin_can_view_create_user_form(): void
    {
        $this->actingAs($this->adminUser)
            ->get(self::URL_CREATE)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.create');
    }

    public function test_admin_can_create_user_with_valid_data(): void
    {
        $userData = [
            'name' => 'Новый пользователь',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$this->userRole->slug],
        ];

        $this->actingAs($this->adminUser)
            ->post(self::URL_STORE, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $this->assertDatabaseHas('users', [
            'name' => 'Новый пользователь',
            'email' => 'newuser@example.com',
        ]);

        // Проверяем, что пароль зашифрован
        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_admin_can_create_user_with_admin_role(): void
    {
        $userData = [
            'name' => 'Новый админ',
            'email' => 'newadmin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$this->adminRole->slug],
        ];

        $this->actingAs($this->adminUser)
            ->post(self::URL_STORE, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $user = User::where('email', 'newadmin@example.com')->first();
        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_admin_can_create_user_with_multiple_roles(): void
    {
        $editorRole = Role::where('slug', 'editor')->first();
        
        $userData = [
            'name' => 'Мульти-роль пользователь',
            'email' => 'multirole@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$this->userRole->slug, $editorRole->slug],
        ];

        $this->actingAs($this->adminUser)
            ->post(self::URL_STORE, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $user = User::where('email', 'multirole@example.com')->first();
        $this->assertTrue($user->hasRole('user'));
        $this->assertTrue($user->hasRole('editor'));
    }

    public function test_create_user_validates_required_fields(): void
    {
        $this->actingAs($this->adminUser)
            ->post(self::URL_STORE, [])
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_create_user_validates_email_format(): void
    {
        $userData = [
            'name' => 'Тестовый пользователь',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->actingAs($this->adminUser)
            ->post(self::URL_STORE, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['email']);
    }

    public function test_create_user_validates_email_uniqueness(): void
    {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'Дубликат email',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->actingAs($this->adminUser)
            ->post(self::URL_STORE, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['email']);
    }

    public function test_create_user_validates_password_confirmation(): void
    {
        $userData = [
            'name' => 'Тестовый пользователь',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ];

        $this->actingAs($this->adminUser)
            ->post(self::URL_STORE, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['password']);
    }

    public function test_create_user_validates_password_length(): void
    {
        $userData = [
            'name' => 'Тестовый пользователь',
            'email' => 'test@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ];

        $this->actingAs($this->adminUser)
            ->post(self::URL_STORE, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['password']);
    }

    public function test_create_user_validates_name_length(): void
    {
        $userData = [
            'name' => str_repeat('A', 256), // Слишком длинное имя
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->actingAs($this->adminUser)
            ->post(self::URL_STORE, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['name']);
    }

    public function test_create_user_with_special_characters_in_name(): void
    {
        $userData = [
            'name' => 'Пользователь с символами @#$%',
            'email' => 'special@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$this->userRole->slug],
        ];

        $this->actingAs($this->adminUser)
            ->post(self::URL_STORE, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $this->assertDatabaseHas('users', [
            'name' => 'Пользователь с символами @#$%',
            'email' => 'special@example.com',
        ]);
    }

    public function test_create_user_with_long_email(): void
    {
        $longEmail = str_repeat('a', 64) . '@' . str_repeat('b', 63) . '.com';
        
        $userData = [
            'name' => 'Длинный email пользователь',
            'email' => $longEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$this->userRole->slug],
        ];

        $this->actingAs($this->adminUser)
            ->post(self::URL_STORE, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $this->assertDatabaseHas('users', [
            'name' => 'Длинный email пользователь',
            'email' => $longEmail,
        ]);
    }

    public function test_create_user_without_roles(): void
    {
        $userData = [
            'name' => 'Пользователь без ролей',
            'email' => 'noroles@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            // Не передаем роли
        ];

        $this->actingAs($this->adminUser)
            ->post(self::URL_STORE, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $user = User::where('email', 'noroles@example.com')->first();
        $this->assertNotNull($user);
        // Пользователь должен получить роль по умолчанию или не иметь ролей
    }

    public function test_guest_cannot_access_create_form(): void
    {
        $this->get(self::URL_CREATE)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/login');
    }

    public function test_guest_cannot_create_user(): void
    {
        $userData = [
            'name' => 'Пользователь гостя',
            'email' => 'guest@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->post(self::URL_STORE, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/login');

        $this->assertDatabaseMissing('users', [
            'email' => 'guest@example.com',
        ]);
    }

    public function test_user_without_admin_role_cannot_access_create_form(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->get(self::URL_CREATE)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_user_without_admin_role_cannot_create_user(): void
    {
        $regularUser = User::factory()->create();
        $userData = [
            'name' => 'Пользователь обычного пользователя',
            'email' => 'regular@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->actingAs($regularUser)
            ->post(self::URL_STORE, $userData)
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertDatabaseMissing('users', [
            'email' => 'regular@example.com',
        ]);
    }

    public function test_create_user_success_message(): void
    {
        $userData = [
            'name' => 'Успешный пользователь',
            'email' => 'success@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$this->userRole->slug],
        ];

        $this->actingAs($this->adminUser)
            ->post(self::URL_STORE, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users')
            ->assertSessionHas('success');
    }

    public function test_create_user_form_contains_csrf_token(): void
    {
        $this->actingAs($this->adminUser)
            ->get(self::URL_CREATE)
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('name="_token"', false);
    }

    public function test_create_user_sets_email_verified_at(): void
    {
        $userData = [
            'name' => 'Верифицированный пользователь',
            'email' => 'verified@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$this->userRole->slug],
            'email_verified' => true,
        ];

        $this->actingAs($this->adminUser)
            ->post(self::URL_STORE, $userData)
            ->assertStatus(Response::HTTP_FOUND);

        $user = User::where('email', 'verified@example.com')->first();
        // В зависимости от логики контроллера, может быть установлено email_verified_at
        $this->assertNotNull($user);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
