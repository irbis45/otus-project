<?php

declare(strict_types=1);

namespace App\Application\Core\News\Repositories;

use App\Models\News;

interface NewsRepositoryInterface
{
    /**
     * @return News[]
     */
    public function fetchAll(): array;

    /**
     * @param bool   $onlyPublished
     * @param int    $limit
     * @param int    $offset
     * @param string $orderBy
     * @param string $direction
     *
     * @return News[]
     */
    public function fetchPaginated(bool $onlyPublished, int $limit, int $offset, string $orderBy = 'id', string $direction = 'desc'): array;

    /**
     * Получить новости по категории
     *
     * @param int    $categoryId
     * @param int    $limit
     * @param int    $offset
     * @param string $orderBy
     * @param string $direction
     *
     * @return array
     */
    public function fetchByCategoryPaginated(int $categoryId, int $limit, int $offset, string $orderBy = 'id', string $direction = 'desc'): array;

    /**
     * @param bool $onlyPublished
     *
     * @return int
     */
    public function count(bool $onlyPublished = false): int;

    /**
     * @param int $id
     *
     * @return News|null
     */
    public function find(int $id): ?News;

    /**
     * @param string $slug
     *
     * @return News|null
     */
    public function findBySlug(string $slug): ?News;

    /**
     * @param News $news
     *
     * @return bool
     */
    public function save(News $news): bool;

    /**
     * @param News $news
     *
     * @return bool|null
     */
    public function delete(News $news): ?bool;

    /**
     * Получить избранные новости
     *
     * @param int $limit
     *
     * @return News[]
     */
    public function fetchFeatured(int $limit): array;

    /**
     * Поиск новостей
     */
    public function searchPaginated(string $query, int $limit, int $offset): array;

    public function countSearch(string $query): int;

    public function countFeatured(): int;

    public function countByCategory(int $categoryId): int;

    /**
     * Увеличить количество просмотров
     */
    public function incrementViews(News $news): void;
}
