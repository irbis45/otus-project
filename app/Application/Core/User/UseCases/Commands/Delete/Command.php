<?php

namespace App\Application\Core\User\UseCases\Commands\Delete;

final readonly class Command
{
    public function __construct(
        public int $id,
    ) {
    }
}
