<?php

namespace App\Application\Core\Comment\DTO;

final readonly class ResultDTO
{
    /**
     * @param CommentDTO[] $results
     */
    public function __construct(
        public array $results
    ) {
    }
}
