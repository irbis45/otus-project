<?php

namespace App\Application\Core\Category\UseCases\Commands\Delete;

final readonly class Command
{
    public function __construct(
        public int $id,
    ) {
    }
}
