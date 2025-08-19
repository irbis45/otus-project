<?php

declare(strict_types=1);

namespace App\Application\Core\Category\Repositories;

use App\Models\Category;

interface CategoryRepositoryInterface
{
    /**
     * @return Category[]
     */
    public function fetchAll(): array;

    /**
     * @param int  $limit
     * @param int  $offset
     * @param bool $onlyActive
     *
     * @return array
     */
    public function fetchPaginated(int $limit, int $offset, bool $onlyActive = false): array;

    /**
     * Получить категории с фильтрами (для админки)
     *
     * @param int    $limit
     * @param int    $offset
     * @param string $orderBy
     * @param string $direction
     * @param string|null $status
     *
     * @return array
     */
    public function fetchPaginatedWithFilters(int $limit, int $offset, string $orderBy = 'id', string $direction = 'desc', ?string $status = null): array;

    /**
     * @return int
     */
    public function count(): int;

    /**
     * Подсчитать категории с фильтрами (для админки)
     *
     * @param string|null $status
     *
     * @return int
     */
    public function countWithFilters(?string $status = null): int;

    /**
     * Поиск категорий
     */
    public function searchPaginated(string $query, int $limit, int $offset, string $orderBy = 'id', string $direction = 'desc'): array;

    public function countSearch(string $query): int;

    /**
     * @param int $id
     *
     * @return Category|null
     */
    public function find(int $id): ?Category;

    /**
     * @param string $name
     *
     * @return bool
     */
    public function existsByName(string $name): bool;

    /**
     * @param Category $category
     *
     * @return bool
     */
    public function save(Category $category): bool;

    /**
     * @param Category $category
     *
     * @return bool|null
     */
    public function delete(Category $category): ?bool;

    /**
     * @param string $slug
     *
     * @return Category|null
     */
    public function findBySlug(string $slug): ?Category;


    /**
     * @param array $ids
     *
     * @return array
     */
    public function findByIds(array $ids): array;

    /**
     * @param int $limit
     *
     * @return Category[]
     */
    public function getPopular(int $limit): array;
}
