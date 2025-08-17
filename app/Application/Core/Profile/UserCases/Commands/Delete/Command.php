<?php

namespace App\Application\Core\Profile\UserCases\Commands\Delete;

final readonly class Command
{
    public function __construct(
        public int $id,
        public string $password,
    ) {
    }
}
