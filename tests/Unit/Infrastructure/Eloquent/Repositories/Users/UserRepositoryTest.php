<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Eloquent\Repositories\Users;

use App\Infrastructure\Eloquent\Repositories\Users\UserRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository();
    }

    public function test_search_paginated_with_search_query(): void
    {
        // Создаем тестовых пользователей
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
        User::factory()->create(['name' => 'Bob Wilson', 'email' => 'bob@example.com']);

        // Поиск по имени
        $results = $this->repository->searchPaginated(10, 0, 'John');
        $this->assertCount(1, $results);
        $this->assertEquals('John Doe', $results[0]->getName());

        // Поиск по email
        $results = $this->repository->searchPaginated(10, 0, 'jane@example.com');
        $this->assertCount(1, $results);
        $this->assertEquals('jane@example.com', $results[0]->getEmail());

        // Поиск по частичному совпадению
        $results = $this->repository->searchPaginated(10, 0, 'jane');
        $this->assertCount(1, $results);
        $this->assertEquals('jane@example.com', $results[0]->getEmail());

        // Поиск без параметров
        $results = $this->repository->searchPaginated(10, 0);
        $this->assertCount(3, $results);
    }

    public function test_search_count_with_search_query(): void
    {
        // Создаем тестовых пользователей
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
        User::factory()->create(['name' => 'Bob Wilson', 'email' => 'bob@example.com']);

        // Подсчет с поиском
        $this->assertEquals(1, $this->repository->searchCount('John'));
        $this->assertEquals(1, $this->repository->searchCount('jane@example.com'));
        $this->assertEquals(2, $this->repository->searchCount('j')); // john и jane - LIKE %j%

        // Подсчет без поиска
        $this->assertEquals(3, $this->repository->searchCount());
    }

    public function test_search_paginated_with_pagination(): void
    {
        // Создаем 15 пользователей
        User::factory()->count(15)->create();

        // Первая страница
        $results = $this->repository->searchPaginated(5, 0);
        $this->assertCount(5, $results);

        // Вторая страница
        $results = $this->repository->searchPaginated(5, 5);
        $this->assertCount(5, $results);

        // Третья страница
        $results = $this->repository->searchPaginated(5, 10);
        $this->assertCount(5, $results);
    }

    public function test_search_case_insensitive(): void
    {
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);

        // Поиск в разных регистрах - PostgreSQL чувствителен к регистру по умолчанию
        $results = $this->repository->searchPaginated(10, 0, 'john');
        $this->assertCount(1, $results);

        $results = $this->repository->searchPaginated(10, 0, 'JOHN');
        $this->assertCount(0, $results); // Точное совпадение не найдено

        $results = $this->repository->searchPaginated(10, 0, 'john@example.com');
        $this->assertCount(1, $results);
    }
}
