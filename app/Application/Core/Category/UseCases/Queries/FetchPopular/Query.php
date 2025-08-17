<?php

namespace App\Application\Core\Category\UseCases\Queries\FetchPopular;

final readonly class Query
{
    public function __construct(
        public int $limit = 10,
    ) {
    }
}
