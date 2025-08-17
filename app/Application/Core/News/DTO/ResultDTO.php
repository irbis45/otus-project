<?php

namespace App\Application\Core\News\DTO;

final readonly class ResultDTO
{
    /**
     * @param NewsDTO[] $results
     */
    public function __construct(
        public array $results
    ) {
    }
}
