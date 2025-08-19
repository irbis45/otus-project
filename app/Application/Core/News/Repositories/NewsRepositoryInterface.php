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
     * Получить новости с фильтрами (для админки)
     *
     * @param int    $limit
     * @param int    $offset
     * @param string $orderBy
     * @param string $direction
     * @param string|null $status
     *
     * @return News[]
     */
    public function fetchPaginatedWithFilters(int $limit, int $offset, string $orderBy = 'id', string $direction = 'desc', ?string $status = null): array;

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
     * Подсчитать новости с фильтрами (для админки)
     *
     * @param string|null $status
     *
     * @return int
     */
    public function countWithFilters(?string $status = null): int;

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
     * Поиск новостей (публичная часть - только опубликованные)
     */
    public function searchPaginated(string $query, int $limit, int $offset, string $orderBy = 'id', string $direction = 'desc'): array;

    /**
     * Поиск новостей для админки (все новости)
     */
    public function searchPaginatedAdmin(string $query, int $limit, int $offset, string $orderBy = 'id', string $direction = 'desc'): array;

    public function countSearch(string $query): int;

    /**
     * Подсчет результатов поиска для админки (все новости)
     */
    public function countSearchAdmin(string $query): int;

    public function countFeatured(): int;

    public function countByCategory(int $categoryId): int;

    /**
     * Увеличить количество просмотров
     */
    public function incrementViews(News $news): void;

    /**
     * Получить список новостей для фильтра (только ID и заголовок)
     *
     * @return array
     */
    public function fetchForFilter(): array;

    /**
     * @param array $ids
     *
     * @return array
     */
    public function findByIds(array $ids): array;

    /**
     * Поиск новостей для автокомплита
     *
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function searchForAutocomplete(string $query, int $limit = 10): array;
}
