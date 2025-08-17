<?php

namespace App\Application\Core\Profile\UserCases\Queries\FetchyByUserId;

final readonly class Query
{
    public function __construct(
        public int $id,
    ) {
    }
}
