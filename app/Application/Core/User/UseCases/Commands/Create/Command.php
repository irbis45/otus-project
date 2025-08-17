<?php

namespace App\Application\Core\User\UseCases\Commands\Create;

final readonly class Command
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $password = null,
    ) {
    }
}
