<?php

namespace App\Application\Core\News\UseCases\Queries\FetchById;

use App\Application\Core\Category\Repositories\CategoryRepositoryInterface;
use App\Application\Core\News\DTO\AuthorDTO;
use App\Application\Core\News\DTO\CategoryDTO;
use App\Application\Core\News\DTO\NewsDTO;
use App\Application\Core\News\Exceptions\NewsNotFoundException;
use App\Application\Core\News\Repositories\NewsRepositoryInterface;
use App\Application\Core\News\Services\ThumbnailService;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Models\News;

class Fetcher
{
    public function __construct(
        private NewsRepositoryInterface $newsRepository,
        private UserRepositoryInterface $userRepository,
        private ThumbnailService $thumbnailService,
        private CategoryRepositoryInterface $categoryRepository
    ) {
    }

    /**
     * @param Query $query
     *
     * @return NewsDTO
     * @throws NewsNotFoundException
     */
    public function fetch(Query $query): NewsDTO
    {
        /** @var ?News $news */
        $news = $this->newsRepository->find($query->id);

        if (!$news) {
            throw new NewsNotFoundException('Новость не найдена');
        }

        $author = $news->getAuthorId() ? $this->userRepository->find($news->getAuthorId()) : null;
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
