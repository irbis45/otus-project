<?php

namespace App\Application\Core\News\UseCases\Commands\Delete;

use App\Application\Contracts\CacheInterface;
use App\Application\Core\News\Exceptions\NewsNotFoundException;
use App\Application\Core\News\Exceptions\NewsSaveException;
use App\Application\Core\News\Repositories\NewsRepositoryInterface;
use App\Application\Core\News\Services\ThumbnailService;

class Handler
{
    public function __construct(
        private NewsRepositoryInterface $newsRepository,
        private ThumbnailService $thumbnailService,
        private CacheInterface $cache
    ) {
    }

    public function handle(Command $command): bool
    {
        $news = $this->newsRepository->find($command->id);

        if (!$news) {
            throw new NewsNotFoundException('Новость не найдена');
        }

        if (!empty($news->thumbnail)) {
            $this->thumbnailService->deleteFile($news->thumbnail);
        }

        $result = $this->newsRepository->delete($news);

        if (!$result) {
            throw new NewsSaveException("Не удалось удалить новость '{$command-> id}'");
        }

        $this->cache->flushTagged('news');
        $this->cache->flushTagged('news_count');

        return $result;
    }
}
