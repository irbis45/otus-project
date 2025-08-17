<?php

namespace App\Application\Core\Comment\UseCases\Queries\FetchByNewsId;

final readonly class Query
{
    public function __construct(
        public int $newsId,
    ) {
    }
}
