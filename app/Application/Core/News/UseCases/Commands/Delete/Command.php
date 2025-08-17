<?php

namespace App\Application\Core\News\UseCases\Commands\Delete;

final readonly class Command
{
    public function __construct(
        public int $id,
    ) {
    }
}
