<?php

namespace App\Application\Core\Profile\UserCases\Commands\Update;

final readonly class Command
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $current_password = null,
        public ?string $password = null,
    ) {
    }
}
