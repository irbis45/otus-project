<?php

namespace App\Application\Core\Category\UseCases\Queries\FetchAllPagination;

final readonly class Query
{
    public function __construct(
        public int $limit = 10,
        public int $offset = 0,
        public bool $onlyActive = false,
    ) {
    }

    public static function fromPage(int $page, int $perPage = 10, bool $onlyActive = false): self
    {
        $offset = ($page - 1) * $perPage;
        return new self(
            limit: $perPage,
            offset: $offset,
            onlyActive: $onlyActive
        );
    }
}
