<?php

namespace App\Application\Core\Comment\UseCases\Commands\Update;

use App\Application\Core\Comment\Enums\CommentStatus;

final readonly class Command
{
    public function __construct(
        public int $id,
        public string $text,
        public CommentStatus $status,
    ) {
    }
}
