<?php

namespace App\Application\Core\User\UseCases\Queries\FetchyById;

final readonly class Query
{
    public function __construct(
        public int $id,
    ) {
    }
}
