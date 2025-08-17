<?php

declare(strict_types=1);

namespace App\Application\Core\Comment\DTO;

final readonly class StatusDTO
{
    public function __construct(
        public string $value,
        public string $label,
    ) {}
}
