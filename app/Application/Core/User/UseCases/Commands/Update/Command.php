<?php

namespace App\Application\Core\User\UseCases\Commands\Update;

final readonly class Command
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $password = null,
    ) {
    }
}
