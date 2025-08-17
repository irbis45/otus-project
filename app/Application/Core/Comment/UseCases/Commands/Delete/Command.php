<?php

namespace App\Application\Core\Comment\UseCases\Commands\Delete;

final readonly class Command
{
    public function __construct(
        public int $id,
    ) {
    }
}
