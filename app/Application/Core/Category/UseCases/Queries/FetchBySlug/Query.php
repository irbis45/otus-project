<?php

namespace App\Application\Core\Category\UseCases\Queries\FetchBySlug;

final readonly class Query
{
    public function __construct(
        public string $slug,
    ) {
    }
}
