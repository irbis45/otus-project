<?php

namespace App\Application\Core\User\DTO;

final readonly class ResultDTO
{
    /**
     * @param UserDTO[] $results
     */
    public function __construct(
        public array $results
    ) {
    }
}
