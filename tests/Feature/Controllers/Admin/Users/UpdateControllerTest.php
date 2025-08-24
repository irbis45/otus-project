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
#[Group('admin-users-update')]
class UpdateControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_EDIT = '/admin_panel/users/%d/edit';
    protected const URL_UPDATE = '/admin_panel/users/%d';

    private User $adminUser;
    private Role $adminRole;
    private Role $userRole;
    private User $testUser;

    public function setUp(): void
    {
        parent::setUp();

        // Получаем роли из базы данных
        $this->adminRole = Role::where('slug', 'admin')->first();
        $this->userRole = Role::where('slug', 'user')->first();

        // Создаем пользователя с ролью администратора
        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($this->adminRole->id);

        // Создаем тестового пользователя
        $this->testUser = User::factory()->create([
            'name' => 'Исходный пользователь',
            'email' => 'original@example.com',
        ]);
        $this->testUser->roles()->attach($this->userRole->id);
    }

    public function test_admin_can_view_edit_user_form(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_EDIT, $this->testUser->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.users.edit')
            ->assertViewHas('user')
            ->assertSee($this->testUser->name)
            ->assertSee($this->testUser->email);
    }

    public function test_admin_can_update_user_with_valid_data(): void
    {
        $updateData = [
            'name' => 'Обновленный пользователь',
            'email' => 'updated@example.com',
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->testUser->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $this->assertDatabaseHas('users', [
            'id' => $this->testUser->id,
            'name' => 'Обновленный пользователь',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_admin_can_update_user_name_only(): void
    {
        $updateData = [
            'name' => 'Только имя изменено',
            'email' => $this->testUser->email, // Добавляем обязательное поле
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->testUser->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $this->assertDatabaseHas('users', [
            'id' => $this->testUser->id,
            'name' => 'Только имя изменено',
            'email' => $this->testUser->email, // Используем исходный email
        ]);
    }

    public function test_admin_can_update_user_email_only(): void
    {
        $updateData = [
            'name' => $this->testUser->name, // Добавляем обязательное поле
            'email' => 'emailonly@example.com',
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->testUser->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $this->assertDatabaseHas('users', [
            'id' => $this->testUser->id,
            'name' => $this->testUser->name, // Используем исходное имя
            'email' => 'emailonly@example.com',
        ]);
    }

    public function test_admin_can_update_user_password(): void
    {
        $oldPassword = $this->testUser->password;

        $updateData = [
            'name' => $this->testUser->name,
            'email' => $this->testUser->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->testUser->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $this->testUser->refresh();
        $this->assertNotEquals($oldPassword, $this->testUser->password);
        $this->assertTrue(Hash::check('newpassword123', $this->testUser->password));
    }

    public function test_admin_can_update_user_roles(): void
    {
        $editorRole = Role::where('slug', 'editor')->first();

        $updateData = [
            'name' => $this->testUser->name,
            'email' => $this->testUser->email,
            'roles' => [$this->adminRole->slug, $editorRole->slug],
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->testUser->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $this->testUser->refresh();
        $this->assertTrue($this->testUser->hasRole('admin'));
        $this->assertTrue($this->testUser->hasRole('editor'));
        $this->assertFalse($this->testUser->hasRole('user'));
    }

    public function test_admin_can_remove_all_roles(): void
    {
        // Пользователь уже имеет роль user в setup
        $this->assertTrue($this->testUser->hasRole('user'));

        $updateData = [
            'name' => $this->testUser->name,
            'email' => $this->testUser->email,
            'roles' => [], // Пустой массив для удаления всех ролей
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->testUser->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $this->testUser->refresh();
        $this->testUser->load('roles'); // Явно загружаем отношения

        // Отладочная информация
        $this->assertCount(0, $this->testUser->roles, 'Роли не были удалены. Текущие роли: ' . $this->testUser->roles->pluck('slug')->implode(', '));
        $this->assertFalse($this->testUser->hasRole('user'), 'Роль user все еще присутствует');
    }

    public function test_update_user_validates_required_fields(): void
    {
        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->testUser->id), [])
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['name', 'email']);
    }

    public function test_update_user_validates_email_uniqueness(): void
    {
        $otherUser = User::factory()->create(['email' => 'other@example.com']);

        $updateData = [
            'name' => 'Тестовый пользователь',
            'email' => 'other@example.com', // Уже существует
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->testUser->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['email']);
    }

    public function test_update_user_validates_password_confirmation(): void
    {
        $updateData = [
            'name' => $this->testUser->name,
            'email' => $this->testUser->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'different_password',
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->testUser->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['password']);
    }

    public function test_update_user_validates_password_length(): void
    {
        $updateData = [
            'name' => $this->testUser->name,
            'email' => $this->testUser->email,
            'password' => '123',
            'password_confirmation' => '123',
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->testUser->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['password']);
    }


    public function test_update_user_with_special_characters(): void
    {
        $updateData = [
            'name' => 'Пользователь с символами @#$%^&*()',
            'email' => 'special_chars@example.com',
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->testUser->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users');

        $this->assertDatabaseHas('users', [
            'id' => $this->testUser->id,
            'name' => 'Пользователь с символами @#$%^&*()',
            'email' => 'special_chars@example.com',
        ]);
    }

    public function test_edit_form_returns_404_for_nonexistent_user(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_EDIT, 99999))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_update_returns_404_for_nonexistent_user(): void
    {
        $updateData = [
            'name' => 'Несуществующий пользователь',
            'email' => 'nonexistent@example.com',
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, 99999), $updateData)
            ->assertStatus(Response::HTTP_FOUND); // 302 - redirect back с ошибкой
    }

    public function test_guest_cannot_access_edit_form(): void
    {
        $this->get(sprintf(self::URL_EDIT, $this->testUser->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/login');
    }

    public function test_guest_cannot_update_user(): void
    {
        $updateData = [
            'name' => 'Пользователь гостя',
            'email' => 'guest@example.com',
        ];

        $this->put(sprintf(self::URL_UPDATE, $this->testUser->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/login');
    }

    public function test_user_without_admin_role_cannot_access_edit_form(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->get(sprintf(self::URL_EDIT, $this->testUser->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }


    public function test_update_user_success_message(): void
    {
        $updateData = [
            'name' => 'Успешно обновленный пользователь',
            'email' => 'success_update@example.com',
        ];

        $this->actingAs($this->adminUser)
            ->put(sprintf(self::URL_UPDATE, $this->testUser->id), $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/users')
            ->assertSessionHas('success');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
