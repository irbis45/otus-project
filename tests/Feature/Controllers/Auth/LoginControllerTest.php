<?php

namespace Tests\Feature\Controllers\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('auth')]
#[Group('auth-login')]
class LoginControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_LOGIN = '/login';
    protected const URL_LOGOUT = '/logout';
    protected const URL_HOME = '/';

    private User $user;
    private string $password;

    public function setUp(): void
    {
        parent::setUp();

        $this->password = 'password123';
        $this->user = User::factory()->create([
            'password' => Hash::make($this->password),
            'email_verified_at' => now(),
        ]);
    }

    public function test_guest_can_view_login_form(): void
    {
        $this->get(self::URL_LOGIN)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('auth.login');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $credentials = [
            'email' => $this->user->email,
            'password' => $this->password,
        ];

        $this->post(self::URL_LOGIN, $credentials)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect(self::URL_HOME);

        $this->assertAuthenticated();
    }

    public function test_user_cannot_login_with_invalid_email(): void
    {
        $credentials = [
            'email' => 'nonexistent@example.com',
            'password' => $this->password,
        ];

        $this->post(self::URL_LOGIN, $credentials)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        $credentials = [
            'email' => $this->user->email,
            'password' => 'wrongpassword',
        ];

        $this->post(self::URL_LOGIN, $credentials)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_user_cannot_login_with_empty_credentials(): void
    {
        $this->post(self::URL_LOGIN, [])
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
    }

    public function test_user_cannot_login_without_email(): void
    {
        $credentials = [
            'password' => $this->password,
        ];

        $this->post(self::URL_LOGIN, $credentials)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_user_cannot_login_without_password(): void
    {
        $credentials = [
            'email' => $this->user->email,
        ];

        $this->post(self::URL_LOGIN, $credentials)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['password']);

        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $this->actingAs($this->user)
            ->post(self::URL_LOGOUT)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect(self::URL_HOME);

        $this->assertGuest();
    }

    public function test_authenticated_user_redirected_after_logout(): void
    {
        $this->actingAs($this->user)
            ->post(self::URL_LOGOUT)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect(self::URL_HOME)
            ->assertSessionHas('success', 'Вы успешно вышли из системы');
    }

    public function test_guest_cannot_access_logout(): void
    {
        $this->post(self::URL_LOGOUT)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect(self::URL_LOGIN);
    }


    public function test_user_redirected_to_home_after_login(): void
    {
        $credentials = [
            'email' => $this->user->email,
            'password' => $this->password,
        ];

        $this->post(self::URL_LOGIN, $credentials)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect(self::URL_HOME)
            ->assertSessionHas('success', 'Добро пожаловать, ' . $this->user->name . '!');
    }

    public function test_authenticated_user_cannot_access_login_form(): void
    {
        $this->actingAs($this->user)
            ->get(self::URL_LOGIN)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect(self::URL_HOME);
    }

    public function test_remember_me_functionality(): void
    {
        $credentials = [
            'email' => $this->user->email,
            'password' => $this->password,
            'remember' => 'on',
        ];

        $this->post(self::URL_LOGIN, $credentials)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect(self::URL_HOME);

        $this->assertAuthenticated();
        // Проверяем, что remember token установлен
        $this->assertNotNull($this->user->fresh()->remember_token);
    }
}
