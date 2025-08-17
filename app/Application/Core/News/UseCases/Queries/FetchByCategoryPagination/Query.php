<?php

namespace App\Application\Core\News\UseCases\Queries\FetchByCategoryPagination;

final readonly class Query
{
    public function __construct(
        public int $categoryId,
        public int $limit = 10,
        public int $offset = 0,
        public string $orderBy = 'id',
        public string $direction = 'desc',
    ) {
    }

    public static function fromPage(int $categoryId, int $page, int $perPage = 10): self
    {
        $offset = ($page - 1) * $perPage;

        return new self(
            categoryId: $categoryId,
            limit: $perPage,
            offset: $offset,
        );
    }
}
