<?php

declare(strict_types=1);

namespace App\Infrastructure\Eloquent\Repositories\Categories;

use App\Application\Core\Category\Repositories\CategoryRepositoryInterface;
use App\Models\Category;


class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @return Category[]
     */
    public function fetchAll(): array {
        return Category::all()->all();
    }

    /**
     * @param int  $limit
     * @param int  $offset
     * @param bool $onlyActive
     *
     * @return array
     */
    public function fetchPaginated(int $limit, int $offset, bool $onlyActive = false): array {

        $query = Category::query();

        if ($onlyActive) {
            $query->active();
        }

        return $query->orderBy('id', 'desc')
                     ->limit($limit)
                     ->offset($offset)
                     ->get()
                     ->all();
    }

    public function fetchPaginatedWithFilters(int $limit, int $offset, string $orderBy = 'id', string $direction = 'desc', ?string $status = null): array
    {
        [$orderBy, $direction] = $this->normalizeOrderBy($orderBy, $direction);

        $query = Category::query();

        if ($status !== null && $status !== '') {
            $query->where('active', $status === '1');
        }

        return $query->orderBy($orderBy, $direction)
                       ->limit($limit)
                       ->offset($offset)
                       ->get()
                       ->all();
    }

    /**
     * @param bool $onlyActive
     *
     * @return int
     */
    public function count(bool $onlyActive = false): int
    {
        $query = Category::query();

        if ($onlyActive) {
            $query->active();
        }

        return $query->count();
    }

    public function countWithFilters(?string $status = null): int
    {
        $query = Category::query();

        if ($status !== null && $status !== '') {
            $query->where('active', $status === '1');
        }

        return $query->count();
    }

    /**
     * @param int $id
     *
     * @return Category|null
     */
    public function find(int $id): ?Category {
        return Category::query()->find($id);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function existsByName(string $name): bool
    {
        return Category::query()->where('name', $name)->exists();
    }

    /**
     * @param Category $category
     *
     * @return bool
     */
    public function save(Category $category): bool {
        return $category->save();
    }

    /**
     * @param Category $category
     *
     * @return bool|null
     */
    public function delete(Category $category): ?bool {
        return $category->delete();
    }


    public function searchPaginated(string $query, int $limit, int $offset, string $orderBy = 'id', string $direction = 'desc'): array
    {
        [$orderBy, $direction] = $this->normalizeOrderBy($orderBy, $direction);

        $categoryQuery = Category::query();

        return $categoryQuery
            ->where(function ($q) use ($query) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($query) . '%'])
                  ->orWhereRaw('LOWER(description) LIKE ?', ['%' . strtolower($query) . '%']);
            })
            ->orderBy($orderBy, $direction)
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->all();
    }

    public function countSearch(string $query): int
    {
        $categoryQuery = Category::query();

        return $categoryQuery
            ->where(function ($q) use ($query) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($query) . '%'])
                  ->orWhereRaw('LOWER(description) LIKE ?', ['%' . strtolower($query) . '%']);
            })
            ->count();
    }

    /**
     * @param string $slug
     *
     * @return Category|null
     */
    public function findBySlug(string $slug): ?Category
    {
        return Category::query()
            ->active()
            ->where('slug', $slug)
            ->first();
    }

    /**
     * @param Category[] $ids
     *
     * @return array
     */
    public function findByIds(array $ids): array
    {
        return Category::query()->whereIn('id', $ids)->get()->keyBy('id')->all();
    }

    /**
     *
     * @param int $limit
     *
     * @return Category[]
     */
    public function getPopular(int $limit): array {
        return Category::query()
                       ->active()
                       ->withCount('publishedNews as news_count')
                       ->orderByDesc('news_count')
                       ->limit($limit)
                       ->get()
                       ->all();
    }

    /**
     * Проверяет и нормализует параметры сортировки
     *
     * @param string $orderBy Запрошенное поле сортировки
     * @param string $direction Запрошенное направление сортировки
     *
     * @return array [orderBy, direction] — валидные значения
     */
    private function normalizeOrderBy(string $orderBy, string $direction): array
    {
        $allowedOrderBy = ['id', 'name', 'created_at'];
        $direction = strtolower($direction);

        if (!in_array($orderBy, $allowedOrderBy, true)) {
            $orderBy = 'id';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        return [$orderBy, $direction];
    }
}
