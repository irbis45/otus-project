<?php

declare(strict_types=1);

namespace App\Application\Core\News\UseCases\Queries\SearchNews;

final readonly class Query
{
    public function __construct(
        public int $limit = 10,
        public int $offset = 0,
        public ?string $search = null,
        public ?string $status = null,
        public string $orderBy = 'id',
        public string $direction = 'desc',
    ) {
    }

    public static function fromRequest(array $params): self
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $orderBy = $params['orderBy'] ?? 'id';
        $direction = $params['direction'] ?? 'desc';

        // Валидация параметров сортировки
        $allowedOrderBy = ['id', 'title', 'created_at', 'published_at'];
        if (!in_array($orderBy, $allowedOrderBy, true)) {
            $orderBy = 'id';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        return new self(
            limit: $perPage,
            offset: $offset,
            search: $params['search'] ?? null,
            status: $params['status'] ?? null,
            orderBy: $orderBy,
            direction: $direction,
        );
    }

    public function hasSearch(): bool
    {
        return $this->search !== null && trim($this->search) !== '';
    }

    public function hasStatusFilter(): bool
    {
        return $this->status !== null && $this->status !== '';
    }
}
