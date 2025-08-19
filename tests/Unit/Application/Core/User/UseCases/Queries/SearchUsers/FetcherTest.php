<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Core\User\UseCases\Queries\SearchUsers;

use App\Application\Core\Role\Repositories\RoleRepositoryInterface;
use App\Application\Core\User\DTO\PaginatedResult;
use App\Application\Core\User\DTO\UserDTO;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Application\Core\User\UseCases\Queries\SearchUsers\Fetcher;
use App\Application\Core\User\UseCases\Queries\SearchUsers\Query;
use App\Models\User;
use DateTimeImmutable;
use Mockery;
use Tests\TestCase;

class FetcherTest extends TestCase
{
    private Fetcher $fetcher;
    private UserRepositoryInterface $userRepository;
    private RoleRepositoryInterface $roleRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->roleRepository = Mockery::mock(RoleRepositoryInterface::class);
        $this->fetcher = new Fetcher($this->userRepository, $this->roleRepository);
    }

    public function test_fetch_with_search_query(): void
    {
        // Создаем тестовых пользователей через factory
        $user1 = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 10:00:00'
        ]);

        $user2 = User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'created_at' => '2024-01-02 10:00:00',
            'updated_at' => '2024-01-02 10:00:00'
        ]);

        $users = [$user1, $user2];
        $rolesByUser = [
            $user1->getId() => ['admin'],
            $user2->getId() => ['user']
        ];

        $query = Query::fromPage(1, 10, 'john');

        // Настройка моков
        $this->userRepository->shouldReceive('searchPaginated')
            ->with(10, 0, 'john')
            ->once()
            ->andReturn($users);

        $this->userRepository->shouldReceive('searchCount')
            ->with('john')
            ->once()
            ->andReturn(2);

        $this->roleRepository->shouldReceive('getByUserIds')
            ->with([$user1->getId(), $user2->getId()])
            ->once()
            ->andReturn($rolesByUser);

        // Выполнение
        $result = $this->fetcher->fetch($query);

        // Проверки
        $this->assertInstanceOf(PaginatedResult::class, $result);
        $this->assertEquals(2, $result->total);
        $this->assertEquals(10, $result->limit);
        $this->assertEquals(0, $result->offset);
        $this->assertCount(2, $result->items);

        // Проверка первого пользователя
        $firstUser = $result->items[0];
        $this->assertInstanceOf(UserDTO::class, $firstUser);
        $this->assertEquals($user1->getId(), $firstUser->id);
        $this->assertEquals('John Doe', $firstUser->name);
        $this->assertEquals('john.doe@example.com', $firstUser->email);
        $this->assertEquals(['admin'], $firstUser->roles);

        // Проверка второго пользователя
        $secondUser = $result->items[1];
        $this->assertEquals($user2->getId(), $secondUser->id);
        $this->assertEquals('Jane Smith', $secondUser->name);
        $this->assertEquals('jane.smith@example.com', $secondUser->email);
        $this->assertEquals(['user'], $secondUser->roles);
    }

    public function test_fetch_without_search_query(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john.doe.test@example.com',
            'created_at' => '2024-01-01 10:00:00'
        ]);

        $users = [$user];
        $rolesByUser = [$user->getId() => ['admin']];

        $query = Query::fromPage(1, 10);

        // Настройка моков
        $this->userRepository->shouldReceive('searchPaginated')
            ->with(10, 0, null)
            ->once()
            ->andReturn($users);

        $this->userRepository->shouldReceive('searchCount')
            ->with(null)
            ->once()
            ->andReturn(1);

        $this->roleRepository->shouldReceive('getByUserIds')
            ->with([$user->getId()])
            ->once()
            ->andReturn($rolesByUser);

        // Выполнение
        $result = $this->fetcher->fetch($query);

        // Проверки
        $this->assertInstanceOf(PaginatedResult::class, $result);
        $this->assertEquals(1, $result->total);
        $this->assertCount(1, $result->items);
    }

    public function test_fetch_with_empty_users(): void
    {
        $query = Query::fromPage(1, 10, 'nonexistent');

        // Настройка моков
        $this->userRepository->shouldReceive('searchPaginated')
            ->with(10, 0, 'nonexistent')
            ->once()
            ->andReturn([]);

        $this->userRepository->shouldReceive('searchCount')
            ->with('nonexistent')
            ->once()
            ->andReturn(0);

        // Роли не должны вызываться для пустого списка
        $this->roleRepository->shouldNotReceive('getByUserIds');

        // Выполнение
        $result = $this->fetcher->fetch($query);

        // Проверки
        $this->assertInstanceOf(PaginatedResult::class, $result);
        $this->assertEquals(0, $result->total);
        $this->assertCount(0, $result->items);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
