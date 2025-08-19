<?php

namespace Tests\Feature\Controllers\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('auth')]
#[Group('auth-register')]
class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_REGISTER = '/register';
    protected const URL_HOME = '/';

    public function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_guest_can_view_register_form(): void
    {
        $this->get(self::URL_REGISTER)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('auth.register');
    }

    public function test_user_can_register_with_valid_data(): void
    {
        $userData = [
            'name' => 'Тестовый Пользователь',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->post(self::URL_REGISTER, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect(self::URL_HOME);

        $this->assertDatabaseHas('users', [
            'name' => 'Тестовый Пользователь',
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_user_cannot_register_with_existing_email(): void
    {
        // Создаем существующего пользователя
        User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'Тестовый Пользователь',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->post(self::URL_REGISTER, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['email']);
    }

    public function test_user_cannot_register_without_name(): void
    {
        $userData = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->post(self::URL_REGISTER, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['name']);
    }

    public function test_user_cannot_register_without_email(): void
    {
        $userData = [
            'name' => 'Тестовый Пользователь',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->post(self::URL_REGISTER, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['email']);
    }

    public function test_user_cannot_register_without_password(): void
    {
        $userData = [
            'name' => 'Тестовый Пользователь',
            'email' => 'test@example.com',
            'password_confirmation' => 'password123',
        ];

        $this->post(self::URL_REGISTER, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['password']);
    }

    public function test_user_cannot_register_without_password_confirmation(): void
    {
        $userData = [
            'name' => 'Тестовый Пользователь',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $this->post(self::URL_REGISTER, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['password']);
    }

    public function test_user_cannot_register_with_mismatched_passwords(): void
    {
        $userData = [
            'name' => 'Тестовый Пользователь',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ];

        $this->post(self::URL_REGISTER, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['password']);
    }

    public function test_user_cannot_register_with_short_password(): void
    {
        $userData = [
            'name' => 'Тестовый Пользователь',
            'email' => 'test@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ];

        $this->post(self::URL_REGISTER, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['password']);
    }

    public function test_user_cannot_register_with_invalid_email_format(): void
    {
        $userData = [
            'name' => 'Тестовый Пользователь',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->post(self::URL_REGISTER, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['email']);
    }

    public function test_user_cannot_register_with_short_name(): void
    {
        $userData = [
            'name' => 'A', // Слишком короткое имя (1 символ, минимум 2)
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->post(self::URL_REGISTER, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['name']);
    }

    public function test_user_cannot_register_with_long_name(): void
    {
        $userData = [
            'name' => str_repeat('A', 256), // Слишком длинное имя
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->post(self::URL_REGISTER, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['name']);
    }

    public function test_authenticated_user_cannot_access_register_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(self::URL_REGISTER)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect(self::URL_HOME);
    }

    public function test_authenticated_user_cannot_register(): void
    {
        $user = User::factory()->create();
        $userData = [
            'name' => 'Тестовый Пользователь',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->actingAs($user)
            ->post(self::URL_REGISTER, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect(self::URL_HOME);
    }

    public function test_user_automatically_logged_in_after_registration(): void
    {
        $userData = [
            'name' => 'Тестовый Пользователь',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->post(self::URL_REGISTER, $userData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect(self::URL_HOME);

        $this->assertAuthenticated();
    }

    public function test_user_created_with_correct_default_values(): void
    {
        $userData = [
            'name' => 'Тестовый Пользователь',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->post(self::URL_REGISTER, $userData);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at); // Email не подтвержден по умолчанию
        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
    }
}
