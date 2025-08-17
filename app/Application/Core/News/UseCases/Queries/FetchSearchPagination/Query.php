<?php

namespace App\Application\Core\News\UseCases\Queries\FetchSearchPagination;

final readonly class Query
{
    public function __construct(
        public string $query,
        public int $limit = 10,
        public int $offset = 0,
    ) {
    }

    public static function fromPage(string $query, int $page, int $perPage = 10): self
    {
        $offset = ($page - 1) * $perPage;

        return new self(
            query: $query,
            limit: $perPage,
            offset: $offset,
        );
    }
}
