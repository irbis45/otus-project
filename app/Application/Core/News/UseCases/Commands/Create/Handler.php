<?php

namespace App\Application\Core\News\UseCases\Commands\Create;

use App\Application\Contracts\CacheInterface;
use App\Application\Core\Category\Repositories\CategoryRepositoryInterface;
use App\Application\Core\News\DTO\AuthorDTO;
use App\Application\Core\News\DTO\CategoryDTO;
use App\Application\Core\News\DTO\NewsDTO;
use App\Application\Core\News\Exceptions\NewsSaveException;
use App\Application\Core\News\Repositories\NewsRepositoryInterface;
use App\Application\Core\News\Services\ThumbnailService;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Models\News;

class Handler
{
    public function __construct(
        private NewsRepositoryInterface $newsRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private UserRepositoryInterface $userRepository,
        private ThumbnailService $thumbnailService,
        private CacheInterface $cache
    ) {
    }

    public function handle(Command $command): NewsDTO
    {
        $news = new News();

        $news->{$news->getColumnName('title')} = $command->title;
        $news->{$news->getColumnName('content')} = $command->content;
        $news->{$news->getColumnName('excerpt')} = $command->excerpt;
        $news->{$news->getColumnName('published_at')} = $command->publishedAt;
        $news->{$news->getColumnName('active')} = $command->active;
        $news->{$news->getColumnName('featured')} = $command->featured;
        $news->{$news->getColumnName('thumbnail')} = $command->thumbnail;

        $news->attachCategory($command->categoryId);
        $news->attachAuthor($command->authorId);

        $result = $this->newsRepository->save($news);

        if (!$result) {
            throw new NewsSaveException("Не удалось сохранить новость '{$command->title}'");
        }

        $this->cache->flushTagged('news');
        $this->cache->flushTagged('news_count');

        $author   = $command->authorId ? $this->userRepository->find($command->authorId) : null;
        $category = $command->categoryId ? $this->categoryRepository->find($command->categoryId) : null;

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
            views:       0,
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
