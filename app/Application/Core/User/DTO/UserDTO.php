<?php

namespace App\Application\Core\User\DTO;

use DateTimeImmutable;

final readonly class UserDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?DateTimeImmutable $createdAt,
        public ?DateTimeImmutable $emailVerifiedAt,
        public ?DateTimeImmutable $updatedAt = null,
        public array $roles = [],
    ) {
    }
}
