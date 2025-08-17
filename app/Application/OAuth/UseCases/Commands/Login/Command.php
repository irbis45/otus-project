<?php

declare(strict_types=1);

namespace App\Application\OAuth\UseCases\Commands\Login;

final readonly class Command
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}
}
