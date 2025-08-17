<?php

declare(strict_types=1);

namespace App\Application\Core\News\UseCases\Commands\TrackView;

use App\Application\Core\News\Repositories\NewsRepositoryInterface;
use App\Application\Contracts\ViewedNewsStorageInterface;

class Handler
{
    public function __construct(private NewsRepositoryInterface $newsRepository, private ViewedNewsStorageInterface $viewedNewsStorage,)
    {}

    public function handle(Command $command): void
    {
        if ($news = $this->newsRepository->find($command->id)) {
            if (!$this->viewedNewsStorage->has($command->id)) {
                $this->newsRepository->incrementViews($news);
                $this->viewedNewsStorage->add($command->id);
            }
        }
    }
}
