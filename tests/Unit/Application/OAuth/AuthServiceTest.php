<?php

namespace Tests\Unit\Application\OAuth;

use App\Application\OAuth\AuthService;
use App\Application\OAuth\Contracts\AuthServiceInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;
    private $mockUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
        $this->mockUser = Mockery::mock(Authenticatable::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_auth_service_implements_interface()
    {
        $this->assertInstanceOf(AuthServiceInterface::class, $this->authService);
    }

    public function test_attempt_with_valid_credentials_returns_user()
    {
        Auth::shouldReceive('attempt')
            ->with(['email' => 'test@example.com', 'password' => 'password'])
            ->once()
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($this->mockUser);

        $result = $this->authService->attempt('test@example.com', 'password');

        $this->assertSame($this->mockUser, $result);
    }

    public function test_attempt_with_invalid_credentials_returns_null()
    {
        Auth::shouldReceive('attempt')
            ->with(['email' => 'test@example.com', 'password' => 'wrong_password'])
            ->once()
            ->andReturn(false);

        $result = $this->authService->attempt('test@example.com', 'wrong_password');

        $this->assertNull($result);
    }

    public function test_attempt_with_empty_credentials()
    {
        Auth::shouldReceive('attempt')
            ->with(['email' => '', 'password' => ''])
            ->once()
            ->andReturn(false);

        $result = $this->authService->attempt('', '');

        $this->assertNull($result);
    }

    public function test_attempt_with_special_characters_in_credentials()
    {
        $email = 'test+tag@example.com';
        $password = 'p@ssw0rd!@#$%^&*()';

        Auth::shouldReceive('attempt')
            ->with(['email' => $email, 'password' => $password])
            ->once()
            ->andReturn(false);

        $result = $this->authService->attempt($email, $password);

        $this->assertNull($result);
    }

    public function test_attempt_with_unicode_credentials()
    {
        $email = 'тест@example.com';
        $password = 'пароль123';

        Auth::shouldReceive('attempt')
            ->with(['email' => $email, 'password' => $password])
            ->once()
            ->andReturn(false);

        $result = $this->authService->attempt($email, $password);

        $this->assertNull($result);
    }

    public function test_attempt_with_long_credentials()
    {
        $email = str_repeat('a', 100) . '@example.com';
        $password = str_repeat('b', 100);

        Auth::shouldReceive('attempt')
            ->with(['email' => $email, 'password' => $password])
            ->once()
            ->andReturn(false);

        $result = $this->authService->attempt($email, $password);

        $this->assertNull($result);
    }

    public function test_attempt_uses_compact_function_correctly()
    {
        $email = 'test@example.com';
        $password = 'password';

        Auth::shouldReceive('attempt')
            ->with(['email' => $email, 'password' => $password])
            ->once()
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($this->mockUser);

        $result = $this->authService->attempt($email, $password);

        $this->assertSame($this->mockUser, $result);
    }

    public function test_attempt_returns_null_when_auth_fails()
    {
        Auth::shouldReceive('attempt')
            ->with(['email' => 'test@example.com', 'password' => 'password'])
            ->once()
            ->andReturn(false);

        $result = $this->authService->attempt('test@example.com', 'password');

        $this->assertNull($result);
    }

    public function test_attempt_returns_user_when_auth_succeeds()
    {
        Auth::shouldReceive('attempt')
            ->with(['email' => 'test@example.com', 'password' => 'password'])
            ->once()
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($this->mockUser);

        $result = $this->authService->attempt('test@example.com', 'password');

        $this->assertSame($this->mockUser, $result);
    }

    public function test_attempt_with_numeric_strings()
    {
        $email = '123@example.com';
        $password = '123456';

        Auth::shouldReceive('attempt')
            ->with(['email' => $email, 'password' => $password])
            ->once()
            ->andReturn(false);

        $result = $this->authService->attempt($email, $password);

        $this->assertNull($result);
    }
}
