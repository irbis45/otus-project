<?php

namespace App\Application\Core\Category\UseCases\Commands\Update;

final readonly class Command
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public bool $active,
    ) {
    }
}
