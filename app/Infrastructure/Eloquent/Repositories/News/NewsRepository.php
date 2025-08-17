<?php

declare(strict_types=1);

namespace App\Infrastructure\Eloquent\Repositories\News;

use App\Application\Core\News\Repositories\NewsRepositoryInterface;
use App\Models\News;

class NewsRepository implements NewsRepositoryInterface
{
    /**
     * @return News[]
     */
    public function fetchAll(): array {
        return News::all()->all();
    }

    /**
     *
     * @param bool   $onlyPublished
     * @param int    $limit
     * @param int    $offset
     * @param string $orderBy
     * @param string $direction
     *
     * @return News[]
     */
    public function fetchPaginated(bool $onlyPublished, int $limit, int $offset, string $orderBy = 'id', string $direction = 'desc'): array {

        [$orderBy, $direction] = $this->normalizeOrderBy($orderBy, $direction);

        $query = News::query();

        if ($onlyPublished) {
            $query->published();
        }

        return $query->orderBy($orderBy, $direction)
                       ->limit($limit)
                       ->offset($offset)
                       ->get()
                       ->all();
    }


    public function fetchByCategoryPaginated(
        int $categoryId,
        int $limit,
        int $offset,
        string $orderBy = 'id',
        string $direction = 'desc'
    ): array {
        [$orderBy, $direction] = $this->normalizeOrderBy($orderBy, $direction);

        return News::query()
                   ->published()
                   ->ofCategory($categoryId)
                   ->orderBy($orderBy, $direction)
                   ->limit($limit)
                   ->offset($offset)
                   ->get()
                   ->all();
    }

    public function countByCategory(int $categoryId): int
    {
        return News::query()
                   ->published()
                   ->ofCategory($categoryId)
                   ->count();
    }

    /**
     * @param bool $onlyPublished
     *
     * @return int
     */
    public function count(bool $onlyPublished = false): int
    {
        $query = News::query();

        if ($onlyPublished) {
            $query->published();
        }

        return $query->count();
    }

    /**
     * @param int $id
     *
     * @return News|null
     */
    public function find(int $id): ?News {
        return News::query()->find($id);
    }


    /**
     * @param string $slug
     *
     * @return News|null
     */
    public function findBySlug(string $slug): ?News {
        return News::query()->where('slug', $slug)
                    ->published()
                    ->first();
    }

    /**
     * @param News $news
     *
     * @return bool
     */
    public function save(News $news): bool {
        return $news->save();
    }

    /**
     * @param News $news
     *
     * @return bool|null
     */
    public function delete(News $news): ?bool {
        return $news->delete();
    }

    public function fetchFeatured(int $limit): array {
        return News::query()
                   ->published()
                   ->featured()
                   ->latest('published_at')
                   //->orderBy('published_at', 'desc')
                   ->limit($limit)
                   ->get()
                   ->all();
    }

    public function countFeatured(): int
    {
        return News::query()
                   ->published()
                   ->featured()
                   ->count();
    }

    public function searchPaginated(string $query, int $limit, int $offset): array
    {
        $newsQ = News::query();

        return $newsQ
            ->published()
            ->where(function ($q) use ($query) {
                $q->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($query) . '%'])
                  ->orWhereRaw('LOWER(content) LIKE ?', ['%' . strtolower($query) . '%']);
            })
            ->latest('published_at')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->all();
    }

    public function countSearch(string $query): int
    {
        $newsQ = News::query();


        return $newsQ
            ->published()
            ->where(function ($q) use ($query) {
                $q->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($query) . '%'])
                  ->orWhereRaw('LOWER(content) LIKE ?', ['%' . strtolower($query) . '%']);
            })
            ->count();
    }

    /**
     * Увеличить количество просмотров
     */
    public function incrementViews(News $news): void
    {
        $news->increment('views');
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
        $allowedOrderBy = ['id', 'created_at', 'title'];
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
