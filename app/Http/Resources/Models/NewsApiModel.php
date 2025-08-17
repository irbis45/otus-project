<?php

declare(strict_types=1);

namespace App\Http\Resources\Models;

class NewsApiModel
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public string $content,
        public ?string $thumbnail,
        public ?string $publishedAt,
        public ?string $createdAt,
        public ?string $excerpt,
        public bool $active,
        public bool $featured,
        public int $views,
        public ?string $updatedAt,
        public ?AuthorApiModel $author,
        public ?CategoryApiModel $category,
    ) {}
}
