<?php

namespace Tests\Unit\Application\OAuth\UseCases\Commands\Login;

use App\Application\Core\Role\Enums\Role as RoleEnum;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Application\OAuth\Contracts\AuthServiceInterface;
use App\Application\OAuth\Exceptions\InvalidCredentialsException;
use App\Application\OAuth\Exceptions\UserNotAuthorizedException;
use App\Application\OAuth\Exceptions\UserNotFoundException;
use App\Application\OAuth\UseCases\Commands\Login\Command;
use App\Application\OAuth\UseCases\Commands\Login\Handler;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Passport\PersonalAccessTokenResult;
use Mockery;
use Tests\TestCase;

class HandlerTest extends TestCase
{
    private Handler $handler;
    private $mockUserRepository;
    private $mockAuthService;
    private $mockUser;
    private $mockTokenResult;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockUserRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->mockAuthService = Mockery::mock(AuthServiceInterface::class);
        $this->mockUser = Mockery::mock(User::class);
        $this->mockTokenResult = Mockery::mock(PersonalAccessTokenResult::class);

        $this->handler = new Handler(
            $this->mockUserRepository,
            $this->mockAuthService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_handler_constructor_injects_dependencies()
    {
        $this->assertInstanceOf(Handler::class, $this->handler);
    }

    public function test_handle_with_valid_admin_user_returns_token()
    {
        $command = new Command('admin@example.com', 'password');
        $accessToken = 'access_token_123';

        $this->mockUserRepository->shouldReceive('findByEmail')
            ->with('admin@example.com')
            ->once()
            ->andReturn($this->mockUser);

        $this->mockUser->shouldReceive('hasRole')
            ->with(RoleEnum::ADMIN->value)
            ->once()
            ->andReturn(true);

        $this->mockAuthService->shouldReceive('attempt')
            ->with('admin@example.com', 'password')
            ->once()
            ->andReturn($this->mockUser);

        $this->mockUser->shouldReceive('createToken')
            ->with('authToken')
            ->once()
            ->andReturn($this->mockTokenResult);

        $this->mockTokenResult->accessToken = $accessToken;

        $result = $this->handler->handle($command);

        $this->assertEquals(['token' => $accessToken], $result);
    }

    public function test_handle_with_valid_editor_user_returns_token()
    {
        $command = new Command('editor@example.com', 'password');
        $accessToken = 'access_token_456';

        $this->mockUserRepository->shouldReceive('findByEmail')
            ->with('editor@example.com')
            ->once()
            ->andReturn($this->mockUser);

        $this->mockUser->shouldReceive('hasRole')
            ->with(RoleEnum::ADMIN->value)
            ->once()
            ->andReturn(false);

        $this->mockUser->shouldReceive('hasRole')
            ->with(RoleEnum::EDITOR->value)
            ->once()
            ->andReturn(true);

        $this->mockAuthService->shouldReceive('attempt')
            ->with('editor@example.com', 'password')
            ->once()
            ->andReturn($this->mockUser);

        $this->mockUser->shouldReceive('createToken')
            ->with('authToken')
            ->once()
            ->andReturn($this->mockTokenResult);

        $this->mockTokenResult->accessToken = $accessToken;

        $result = $this->handler->handle($command);

        $this->assertEquals(['token' => $accessToken], $result);
    }

    public function test_handle_throws_user_not_found_exception()
    {
        $command = new Command('nonexistent@example.com', 'password');

        $this->mockUserRepository->shouldReceive('findByEmail')
            ->with('nonexistent@example.com')
            ->once()
            ->andReturn(null);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Пользователь не найден');

        $this->handler->handle($command);
    }

    public function test_handle_throws_user_not_authorized_exception_for_user_role()
    {
        $command = new Command('user@example.com', 'password');

        $this->mockUserRepository->shouldReceive('findByEmail')
            ->with('user@example.com')
            ->once()
            ->andReturn($this->mockUser);

        $this->mockUser->shouldReceive('hasRole')
            ->with(RoleEnum::ADMIN->value)
            ->once()
            ->andReturn(false);

        $this->mockUser->shouldReceive('hasRole')
            ->with(RoleEnum::EDITOR->value)
            ->once()
            ->andReturn(false);

        $this->expectException(UserNotAuthorizedException::class);

        $this->handler->handle($command);
    }

    public function test_handle_throws_invalid_credentials_exception()
    {
        $command = new Command('admin@example.com', 'wrong_password');

        $this->mockUserRepository->shouldReceive('findByEmail')
            ->with('admin@example.com')
            ->once()
            ->andReturn($this->mockUser);

        $this->mockUser->shouldReceive('hasRole')
            ->with(RoleEnum::ADMIN->value)
            ->once()
            ->andReturn(true);

        $this->mockAuthService->shouldReceive('attempt')
            ->with('admin@example.com', 'wrong_password')
            ->once()
            ->andReturn(null);

        $this->expectException(InvalidCredentialsException::class);

        $this->handler->handle($command);
    }

    public function test_handle_with_empty_credentials()
    {
        $command = new Command('', '');

        $this->mockUserRepository->shouldReceive('findByEmail')
            ->with('')
            ->once()
            ->andReturn(null);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Пользователь не найден');

        $this->handler->handle($command);
    }

    public function test_handle_with_special_characters_in_credentials()
    {
        $email = 'test+tag@example.com';
        $password = 'p@ssw0rd!@#$%^&*()';
        $command = new Command($email, $password);

        $this->mockUserRepository->shouldReceive('findByEmail')
            ->with($email)
            ->once()
            ->andReturn($this->mockUser);

        $this->mockUser->shouldReceive('hasRole')
            ->with(RoleEnum::ADMIN->value)
            ->once()
            ->andReturn(true);

        $this->mockAuthService->shouldReceive('attempt')
            ->with($email, $password)
            ->once()
            ->andReturn($this->mockUser);

        $this->mockUser->shouldReceive('createToken')
            ->with('authToken')
            ->once()
            ->andReturn($this->mockTokenResult);

        $this->mockTokenResult->accessToken = 'token';

        $result = $this->handler->handle($command);

        $this->assertArrayHasKey('token', $result);
    }

    public function test_handle_uses_correct_token_name()
    {
        $command = new Command('admin@example.com', 'password');
        $accessToken = 'test_access_token';

        $this->mockUserRepository->shouldReceive('findByEmail')
            ->with('admin@example.com')
            ->once()
            ->andReturn($this->mockUser);

        $this->mockUser->shouldReceive('hasRole')
            ->with(RoleEnum::ADMIN->value)
            ->once()
            ->andReturn(true);

        $this->mockAuthService->shouldReceive('attempt')
            ->with('admin@example.com', 'password')
            ->once()
            ->andReturn($this->mockUser);

        $this->mockUser->shouldReceive('createToken')
            ->with('authToken')
            ->once()
            ->andReturn($this->mockTokenResult);

        $this->mockTokenResult->accessToken = $accessToken;

        $result = $this->handler->handle($command);

        $this->assertArrayHasKey('token', $result);
    }
}
