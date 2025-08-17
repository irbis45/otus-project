<?php

namespace App\Application\Core\Category\UseCases\Commands\Create;

final readonly class Command
{
    public function __construct(
        public string $name,
        public ?string $description,
        public bool $active,
    ) {
    }
}
