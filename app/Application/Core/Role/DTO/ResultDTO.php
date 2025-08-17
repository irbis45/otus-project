<?php

declare(strict_types=1);

namespace App\Application\Core\Role\DTO;

final readonly class ResultDTO
{
    /**
     * @param RoleDTO[] $results
     */
    public function __construct(
        public array $results
    ) {
    }
}
