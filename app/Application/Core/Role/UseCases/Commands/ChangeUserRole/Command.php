<?php

declare(strict_types=1);

namespace App\Application\Core\Role\UseCases\Commands\ChangeUserRole;

final readonly class Command
{
    public function __construct(
        public int $userId,
        public array $roleSlugs,
    ) {}
}
