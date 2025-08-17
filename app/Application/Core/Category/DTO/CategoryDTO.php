<?php

namespace App\Application\Core\Category\DTO;

final readonly class CategoryDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public string $slug,
        public bool $active,
        public ?int $newsCount = null,
    ) {
    }
}
