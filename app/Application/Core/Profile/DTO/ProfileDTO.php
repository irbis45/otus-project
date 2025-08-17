<?php

namespace App\Application\Core\Profile\DTO;

final readonly class ProfileDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
    ) {
    }
}
