<?php

namespace Tests\Unit\Application\OAuth\UseCases\Commands\Register;

use App\Application\Core\Role\Enums\Role;
use App\Application\Core\Role\Repositories\RoleRepositoryInterface;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Application\OAuth\Exceptions\UserSaveException;
use App\Application\OAuth\UseCases\Commands\Register\Command;
use App\Application\OAuth\UseCases\Commands\Register\Handler;
use App\Infrastructure\PasswordHasher\LaravelPasswordHasher;
use App\Models\Role as RoleModel;
use App\Models\User;
use Mockery;
use Tests\TestCase;

class HandlerTest extends TestCase
{
    private Handler $handler;
    private $mockUserRepository;
    private $mockRoleRepository;
    private $mockPasswordHasher;
    private $mockRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockUserRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->mockRoleRepository = Mockery::mock(RoleRepositoryInterface::class);
        $this->mockPasswordHasher = Mockery::mock(LaravelPasswordHasher::class);
        $this->mockRole = Mockery::mock(RoleModel::class);

        $this->handler = new Handler(
            $this->mockUserRepository,
            $this->mockRoleRepository,
            $this->mockPasswordHasher
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

    public function test_handle_throws_user_save_exception_when_save_fails()
    {
        $command = new Command('John Doe', 'john@example.com', 'password123');

        // Mock password hasher
        $this->mockPasswordHasher->shouldReceive('hash')
            ->with('password123')
            ->once()
            ->andReturn('hashed_password');

        // Mock user repository save failure
        $this->mockUserRepository->shouldReceive('save')
            ->with(Mockery::type(User::class))
            ->once()
            ->andReturn(false);

        $this->expectException(UserSaveException::class);
        $this->expectExceptionMessage('Не удалось сохранить токен');

        $this->handler->handle($command);
    }

    public function test_handle_with_special_characters_in_data()
    {
        $command = new Command('John Doe Jr.', 'john+tag@example.com', 'p@ssw0rd!@#');

        // Mock password hasher
        $this->mockPasswordHasher->shouldReceive('hash')
            ->with('p@ssw0rd!@#')
            ->once()
            ->andReturn('hashed_password');

        // Mock user repository save failure (чтобы избежать создания реального пользователя)
        $this->mockUserRepository->shouldReceive('save')
            ->with(Mockery::type(User::class))
            ->once()
            ->andReturn(false);

        $this->expectException(UserSaveException::class);
        $this->expectExceptionMessage('Не удалось сохранить токен');

        $this->handler->handle($command);
    }

    public function test_handle_with_unicode_characters()
    {
        $command = new Command('Иван Петров', 'иван@example.com', 'пароль123');

        // Mock password hasher
        $this->mockPasswordHasher->shouldReceive('hash')
            ->with('пароль123')
            ->once()
            ->andReturn('hashed_password');

        // Mock user repository save failure
        $this->mockUserRepository->shouldReceive('save')
            ->with(Mockery::type(User::class))
            ->once()
            ->andReturn(false);

        $this->expectException(UserSaveException::class);
        $this->expectExceptionMessage('Не удалось сохранить токен');

        $this->handler->handle($command);
    }

    public function test_handle_with_empty_strings()
    {
        $command = new Command('', '', '');

        // Mock password hasher
        $this->mockPasswordHasher->shouldReceive('hash')
            ->with('')
            ->once()
            ->andReturn('hashed_password');

        // Mock user repository save failure
        $this->mockUserRepository->shouldReceive('save')
            ->with(Mockery::type(User::class))
            ->once()
            ->andReturn(false);

        $this->expectException(UserSaveException::class);
        $this->expectExceptionMessage('Не удалось сохранить токен');

        $this->handler->handle($command);
    }

    public function test_handle_with_long_strings()
    {
        $command = new Command(
            str_repeat('A', 100),
            str_repeat('a', 100) . '@example.com',
            str_repeat('b', 100)
        );

        // Mock password hasher
        $this->mockPasswordHasher->shouldReceive('hash')
            ->with(str_repeat('b', 100))
            ->once()
            ->andReturn('hashed_password');

        // Mock user repository save failure
        $this->mockUserRepository->shouldReceive('save')
            ->with(Mockery::type(User::class))
            ->once()
            ->andReturn(false);

        $this->expectException(UserSaveException::class);
        $this->expectExceptionMessage('Не удалось сохранить токен');

        $this->handler->handle($command);
    }

    public function test_handle_password_is_hashed()
    {
        $command = new Command('John Doe', 'john@example.com', 'password123');
        $hashedPassword = 'securely_hashed_password';

        // Mock password hasher to verify it's called with correct password
        $this->mockPasswordHasher->shouldReceive('hash')
            ->with('password123')
            ->once()
            ->andReturn($hashedPassword);

        // Mock user repository save
        $this->mockUserRepository->shouldReceive('save')
            ->with(Mockery::type(User::class))
            ->once()
            ->andReturn(false);

        $this->expectException(UserSaveException::class);

        $this->handler->handle($command);
    }


    public function test_handle_searches_for_user_role()
    {
        $command = new Command('John Doe', 'john@example.com', 'password123');

        // Mock password hasher
        $this->mockPasswordHasher->shouldReceive('hash')
            ->with('password123')
            ->once()
            ->andReturn('hashed_password');

        // Mock user repository save to succeed initially
        $this->mockUserRepository->shouldReceive('save')
            ->with(Mockery::type(User::class))
            ->once()
            ->andReturn(true);

        // Verify that it searches for USER role
        $this->mockRoleRepository->shouldReceive('findBySlugs')
            ->with([Role::USER->value])
            ->once()
            ->andReturn([]);

        // Since we can't easily mock createToken without database, expect exception
        $this->expectException(\Exception::class);

        $this->handler->handle($command);
    }
}
