<?php

namespace Tests\Unit\Application\OAuth\UseCases\Commands\Logout;

use App\Application\OAuth\Contracts\OAuthRefreshTokenRepositoryInterface;
use App\Application\OAuth\Contracts\OAuthTokenRepositoryInterface;
use App\Application\OAuth\UseCases\Commands\Logout\Command;
use App\Application\OAuth\UseCases\Commands\Logout\Handler;
use Mockery;
use Tests\TestCase;

class HandlerTest extends TestCase
{
    private Handler $handler;
    private $mockTokenRepository;
    private $mockRefreshTokenRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockTokenRepository = Mockery::mock(OAuthTokenRepositoryInterface::class);
        $this->mockRefreshTokenRepository = Mockery::mock(OAuthRefreshTokenRepositoryInterface::class);
        
        $this->handler = new Handler(
            $this->mockTokenRepository,
            $this->mockRefreshTokenRepository
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

    public function test_handle_revokes_access_token()
    {
        $command = new Command('token123');

        $this->mockTokenRepository->shouldReceive('revokeAccessToken')
            ->with('token123')
            ->once();

        $this->mockRefreshTokenRepository->shouldReceive('revokeRefreshTokensByAccessTokenId')
            ->with('token123')
            ->once();

        $this->handler->handle($command);
        
        $this->assertTrue(true); // Тест проходит, если не выбрасывается исключение
    }

    public function test_handle_with_empty_token_id()
    {
        $command = new Command('');

        $this->mockTokenRepository->shouldReceive('revokeAccessToken')
            ->with('')
            ->once();

        $this->mockRefreshTokenRepository->shouldReceive('revokeRefreshTokensByAccessTokenId')
            ->with('')
            ->once();

        $this->handler->handle($command);
        
        $this->assertTrue(true); // Тест проходит, если не выбрасывается исключение
    }

    public function test_handle_with_special_characters_in_token_id()
    {
        $tokenId = 'token@#$%^&*()_+-=[]{}|;:,.<>?';
        $command = new Command($tokenId);

        $this->mockTokenRepository->shouldReceive('revokeAccessToken')
            ->with($tokenId)
            ->once();

        $this->mockRefreshTokenRepository->shouldReceive('revokeRefreshTokensByAccessTokenId')
            ->with($tokenId)
            ->once();

        $this->handler->handle($command);
        
        $this->assertTrue(true); // Тест проходит, если не выбрасывается исключение
    }

    public function test_handle_with_unicode_characters_in_token_id()
    {
        $tokenId = 'токен123';
        $command = new Command($tokenId);

        $this->mockTokenRepository->shouldReceive('revokeAccessToken')
            ->with($tokenId)
            ->once();

        $this->mockRefreshTokenRepository->shouldReceive('revokeRefreshTokensByAccessTokenId')
            ->with($tokenId)
            ->once();

        $this->handler->handle($command);
        
        $this->assertTrue(true); // Тест проходит, если не выбрасывается исключение
    }

    public function test_handle_with_long_token_id()
    {
        $tokenId = str_repeat('a', 1000);
        $command = new Command($tokenId);

        $this->mockTokenRepository->shouldReceive('revokeAccessToken')
            ->with($tokenId)
            ->once();

        $this->mockRefreshTokenRepository->shouldReceive('revokeRefreshTokensByAccessTokenId')
            ->with($tokenId)
            ->once();

        $this->handler->handle($command);
        
        $this->assertTrue(true); // Тест проходит, если не выбрасывается исключение
    }

    public function test_handle_with_numeric_token_id()
    {
        $tokenId = '123456';
        $command = new Command($tokenId);

        $this->mockTokenRepository->shouldReceive('revokeAccessToken')
            ->with($tokenId)
            ->once();

        $this->mockRefreshTokenRepository->shouldReceive('revokeRefreshTokensByAccessTokenId')
            ->with($tokenId)
            ->once();

        $this->handler->handle($command);
        
        $this->assertTrue(true); // Тест проходит, если не выбрасывается исключение
    }

    public function test_handle_with_uuid_format_token_id()
    {
        $tokenId = '550e8400-e29b-41d4-a716-446655440000';
        $command = new Command($tokenId);

        $this->mockTokenRepository->shouldReceive('revokeAccessToken')
            ->with($tokenId)
            ->once();

        $this->mockRefreshTokenRepository->shouldReceive('revokeRefreshTokensByAccessTokenId')
            ->with($tokenId)
            ->once();

        $this->handler->handle($command);
        
        $this->assertTrue(true); // Тест проходит, если не выбрасывается исключение
    }

    public function test_handle_with_jwt_format_token_id()
    {
        $tokenId = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c';
        $command = new Command($tokenId);

        $this->mockTokenRepository->shouldReceive('revokeAccessToken')
            ->with($tokenId)
            ->once();

        $this->mockRefreshTokenRepository->shouldReceive('revokeRefreshTokensByAccessTokenId')
            ->with($tokenId)
            ->once();

        $this->handler->handle($command);
        
        $this->assertTrue(true); // Тест проходит, если не выбрасывается исключение
    }

    public function test_handle_calls_repositories_in_correct_order()
    {
        $command = new Command('token123');

        $this->mockTokenRepository->shouldReceive('revokeAccessToken')
            ->with('token123')
            ->once()
            ->ordered();

        $this->mockRefreshTokenRepository->shouldReceive('revokeRefreshTokensByAccessTokenId')
            ->with('token123')
            ->once()
            ->ordered();

        $this->handler->handle($command);
        
        $this->assertTrue(true); // Тест проходит, если не выбрасывается исключение
    }

    public function test_handle_returns_void()
    {
        $command = new Command('token123');

        $this->mockTokenRepository->shouldReceive('revokeAccessToken')
            ->with('token123')
            ->once();

        $this->mockRefreshTokenRepository->shouldReceive('revokeRefreshTokensByAccessTokenId')
            ->with('token123')
            ->once();

        $result = $this->handler->handle($command);

        $this->assertNull($result);
    }

    public function test_handle_with_whitespace_in_token_id()
    {
        $tokenId = ' token with spaces ';
        $command = new Command($tokenId);

        $this->mockTokenRepository->shouldReceive('revokeAccessToken')
            ->with($tokenId)
            ->once();

        $this->mockRefreshTokenRepository->shouldReceive('revokeRefreshTokensByAccessTokenId')
            ->with($tokenId)
            ->once();

        $this->handler->handle($command);
        
        $this->assertTrue(true); // Тест проходит, если не выбрасывается исключение
    }

    public function test_handle_with_newlines_in_token_id()
    {
        $tokenId = "token\nwith\nnewlines";
        $command = new Command($tokenId);

        $this->mockTokenRepository->shouldReceive('revokeAccessToken')
            ->with($tokenId)
            ->once();

        $this->mockRefreshTokenRepository->shouldReceive('revokeRefreshTokensByAccessTokenId')
            ->with($tokenId)
            ->once();

        $this->handler->handle($command);
        
        $this->assertTrue(true); // Тест проходит, если не выбрасывается исключение
    }

    public function test_handle_does_not_throw_exceptions()
    {
        $command = new Command('token123');

        $this->mockTokenRepository->shouldReceive('revokeAccessToken')
            ->with('token123')
            ->once();

        $this->mockRefreshTokenRepository->shouldReceive('revokeRefreshTokensByAccessTokenId')
            ->with('token123')
            ->once();

        // Should not throw any exceptions
        $this->handler->handle($command);
        
        $this->assertTrue(true); // Тест проходит, если не выбрасывается исключение
        
        $this->assertTrue(true);
    }
}
