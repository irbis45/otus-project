<?php

namespace App\Application\Core\News\UseCases\Commands\Create;
use DateTimeImmutable;

final readonly class Command
{
    public function __construct(
        public string $title,
        public string $content,
        public ?string $excerpt,
        public int $authorId,
        public int $categoryId,
        public DateTimeImmutable $publishedAt,
        public bool $active = false,
        public bool $featured = false,
        public ?string $thumbnail = null,
    ) {
    }
}
