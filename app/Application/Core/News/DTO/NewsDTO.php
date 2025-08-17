<?php

namespace App\Application\Core\News\DTO;

use DateTimeImmutable;

final readonly class NewsDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public string $content,
        public ?string $thumbnail,
        public ?DateTimeImmutable $publishedAt,
        public ?DateTimeImmutable $createdAt,
        public ?string $excerpt = null,
        public bool $active = true,
        public bool $featured = false,
        public int $views = 0,
        public ?DateTimeImmutable $updatedAt = null,
        public ?AuthorDTO $author = null,
        public ?CategoryDTO $category = null,
    ) {
    }
}
