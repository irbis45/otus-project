<?php

namespace App\Application\Core\News\UseCases\Queries\FetchFeatured;

final readonly class Query
{
    public function __construct(
        public int $limit = 3,
    ) {
    }
}
