<?php

namespace App\Application\Core\Category\DTO;

final readonly class ResultDTO
{
    /**
     * @param CategoryDTO[] $results
     */
    public function __construct(
        public array $results
    ) {
    }
}
