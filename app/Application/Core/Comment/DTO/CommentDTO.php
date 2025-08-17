<?php

namespace App\Application\Core\Comment\DTO;

use DateTimeImmutable;

final readonly class CommentDTO
{
    public function __construct(
        public int $id,
        public string $text,
        public ?AuthorDTO $author,
        public int $newsId,
        public ?StatusDTO $status,
        public ?int $parentId,
        public ?DateTimeImmutable $createdAt,
        public ?DateTimeImmutable $updatedAt = null,
        public array $replies = [],
    ) {
    }
}
