<?php

namespace App\Application\Core\News\UseCases\Commands\Update;

use App\Application\Contracts\CacheInterface;
use App\Application\Core\Category\Repositories\CategoryRepositoryInterface;
use App\Application\Core\News\DTO\AuthorDTO;
use App\Application\Core\News\DTO\CategoryDTO;
use App\Application\Core\News\DTO\NewsDTO;
use App\Application\Core\News\Exceptions\NewsNotFoundException;
use App\Application\Core\News\Repositories\NewsRepositoryInterface;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Application\Core\News\Services\ThumbnailService;

class Handler
{
    public function __construct(
        private NewsRepositoryInterface $newsRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private UserRepositoryInterface $userRepository,
        private ThumbnailService $thumbnailService,
        private CacheInterface $cache,
    ) {
    }

    /**
     * @param Command $command
     *
     * @return NewsDTO
     * @throws NewsNotFoundException
     */
    public function handle(Command $command): NewsDTO
    {
        $news = $this->newsRepository->find($command->id);

        if ( ! $news) {
            throw new NewsNotFoundException('Новость не найдена');
        }

        $isChangedActivityCount = $news->getActive() !== $command->active;

        $oldThumbnail = $news->getThumbnail();

        // Удаление старого файла, если нужно удалить или заменяем новым
        if ($command->deleteThumbnail && $oldThumbnail) {
            $this->thumbnailService->deleteFile($oldThumbnail);
            $news->{$news->getColumnName('thumbnail')} = null;
        }

        if ($command->thumbnail !== null) {
            if ($oldThumbnail) {
                $this->thumbnailService->deleteFile($oldThumbnail);
            }
            $news->{$news->getColumnName('thumbnail')} = $command->thumbnail;
        }

        if ($command->title !== null) {
            $news->{$news->getColumnName('title')} = $command->title;
        }
        if ($command->content !== null) {
            $news->{$news->getColumnName('content')} = $command->content;
        }
        if ($command->excerpt !== null) {
            $news->{$news->getColumnName('excerpt')} = $command->excerpt;
        }
        if ($command->publishedAt !== null) {
            $news->{$news->getColumnName('published_at')} = $command->publishedAt;
        }
        if ($command->active !== null) {
            $news->{$news->getColumnName('active')} = $command->active;
        }
        if ($command->featured !== null) {
            $news->{$news->getColumnName('featured')} = $command->featured;
        }
        if ($command->categoryId !== null) {
            $news->attachCategory($command->categoryId);
        }

        $this->newsRepository->save($news);

        $this->cache->flushTagged('news');

        if ($isChangedActivityCount) {
            $this->cache->flushTagged('news_count');
        }

        $author   = $news->getAuthorId() ? $this->userRepository->find($news->getAuthorId()) : null;
        $category = $news->getCategoryId() ? $this->categoryRepository->find($news->getCategoryId()) : null;

        return new NewsDTO(
            id:          $news->getId(),
            title:       $news->getTitle(),
            slug:        $news->getSlug(),
            content:     $news->getContent(),
            thumbnail:   $this->thumbnailService->getPublicUrl($news->getThumbnail()),
            publishedAt: $news->getPublishedAt() ? new \DateTimeImmutable($news->getPublishedAt()) : null,
            createdAt:   $news->getCreatedAt() ? new \DateTimeImmutable($news->getCreatedAt()) : null,
            excerpt:     $news->getExcerpt(),
            active:      $news->getActive(),
            featured:    $news->getFeatured(),
            views:       $news->getViews(),
            updatedAt:   $news->getUpdatedAt() ? new \DateTimeImmutable($news->getUpdatedAt()) : null,
            author:      $author ? new AuthorDTO(
                             id: $author->getId(),
                             name: $author->getName(),
                             email: $author->getEmail(),
                         ) : null,
            category:    $category ? new CategoryDTO(
                             id: $category->getId(),
                             name: $category->getName(),
                             slug: $category->getSlug(),
                         ) : null,
        );
    }
}
