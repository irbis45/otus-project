<?php

namespace Tests\Feature\Controllers\Api\V1\AuthController;

use App\Application\OAuth\Contracts\OAuthRefreshTokenRepositoryInterface;
use App\Application\OAuth\Contracts\OAuthTokenRepositoryInterface;
use App\Application\OAuth\UseCases\Commands\Logout\Handler as LogoutHandler;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('api')]
#[Group('api-auth')]
class LogoutTest extends TestCase
{
    use RefreshDatabase;
    private const URL = '/api/v1/logout';
    private const GUARD = 'api';


    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }


    public function test_logout_success(): void
    {
        $user = User::factory()->create();

        // Аутентифицируем пользователя через Passport::actingAs с правильным guard
        Passport::actingAs($user, [], self::GUARD);

        // Мокаем метод token() у пользователя, чтобы вернуть объект с нужным tokenId
        $fakeTokenId = 'fake-token-id';
        $userMock = Mockery::mock(User::class, \Illuminate\Contracts\Auth\Authenticatable::class)->makePartial();
        $userMock->shouldReceive('token')->andReturn((object)['id' => $fakeTokenId]);

        // Подменяем текущего аутентифицированного пользователя мок-объектом с токеном
        $this->be($userMock, self::GUARD);

        $mockTokenRepo = Mockery::mock(OAuthTokenRepositoryInterface::class);
        $mockTokenRepo->shouldReceive('revokeAccessToken')
                      ->once()
                      ->with($fakeTokenId);

        $mockRefreshTokenRepo = Mockery::mock(OAuthRefreshTokenRepositoryInterface::class);
        $mockRefreshTokenRepo->shouldReceive('revokeRefreshTokensByAccessTokenId')
                             ->once()
                             ->with($fakeTokenId);

        $logoutHandler = new LogoutHandler($mockTokenRepo, $mockRefreshTokenRepo);

        $this->app->instance(LogoutHandler::class, $logoutHandler);

        $response = $this->postJson(self::URL);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(['message' => 'Logged out']);
    }

    public function test_logout_handler_exception(): void
    {
        $user = User::factory()->create();

        Passport::actingAs($user, [], self::GUARD);

        $fakeTokenId = 'fake-token-id';

        $userPartialMock = Mockery::mock(User::class, \Illuminate\Contracts\Auth\Authenticatable::class)->makePartial();
        $userPartialMock->shouldReceive('token')->andReturn((object)['id' => $fakeTokenId]);

        //auth('api_v2')->setUser($userPartialMock);
        $this->be($userPartialMock, self::GUARD);

        $mockHandler = Mockery::mock(LogoutHandler::class);
        $mockHandler->shouldReceive('handle')
                    ->once()
                    ->andThrow(new Exception('Something went wrong'));

        $this->app->instance(LogoutHandler::class, $mockHandler);

        $response = $this->postJson(self::URL);

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
                 ->assertJson(['message' => 'Logout failed']);
    }

    public function test_logout_unauthenticated(): void
    {
        $response = $this->postJson(self::URL);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}
