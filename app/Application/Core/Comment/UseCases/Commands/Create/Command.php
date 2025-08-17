<?php

namespace App\Application\Core\Comment\UseCases\Commands\Create;

final readonly class Command
{
    public function __construct(
        public string $text,
        public int $authorId,
        public int $newsId,
        public ?int $parentId = null,
    ) {
    }
}
