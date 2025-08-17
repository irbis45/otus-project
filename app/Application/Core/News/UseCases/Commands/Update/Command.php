<?php

namespace App\Application\Core\News\UseCases\Commands\Update;
use DateTimeImmutable;

final readonly class Command
{
    public function __construct(
        public int $id,
        public ?string $title = null,
        public ?string $content = null,
        public ?string $excerpt = null,
        public ?int $categoryId = null,
        public ?DateTimeImmutable $publishedAt = null,
        public ?bool $active = null,
        public ?bool $featured = null,
        public ?string $thumbnail = null,
        public ?bool $deleteThumbnail = null,
    ) {
    }
}
