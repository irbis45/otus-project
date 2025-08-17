<?php

declare(strict_types=1);

namespace App\Application\Core\News\UseCases\Commands\TrackView;

final readonly class Command
{
    public function __construct(
        public int $id,
    ) {}
}
