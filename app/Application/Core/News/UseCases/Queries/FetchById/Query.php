<?php

namespace App\Application\Core\News\UseCases\Queries\FetchById;

final readonly class Query
{
    public function __construct(
        public int $id,
    ) {
    }
}
