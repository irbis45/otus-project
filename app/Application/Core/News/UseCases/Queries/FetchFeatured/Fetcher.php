<?php

namespace App\Application\Core\News\UseCases\Queries\FetchFeatured;

use App\Application\Contracts\CacheInterface;
use App\Application\Core\Category\Repositories\CategoryRepositoryInterface;
use App\Application\Core\News\DTO\AuthorDTO;
use App\Application\Core\News\DTO\CategoryDTO;
use App\Application\Core\News\DTO\NewsDTO;
use App\Application\Core\News\DTO\ResultDTO;
use App\Application\Core\News\Repositories\NewsRepositoryInterface;
use App\Application\Core\News\Services\ThumbnailService;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Models\News;

/**
 * #deprecated
 */
class Fetcher
{
    public function __construct(
        private NewsRepositoryInterface $newsRepository,
        private UserRepositoryInterface $userRepository,
        private ThumbnailService $thumbnailService,
        private CategoryRepositoryInterface $categoryRepository,
        private CacheInterface $cache
    ) {
    }

    /**
     * @param Query $query
     *
     * @return ResultDTO
     */
    public function fetch(Query $query): ResultDTO
    {
        $newsItems = $this->cache->rememberWithTag(
            'news',
            'featured_news_list',
            config('cache.news.featured'),
            function () use ($query) {
                return $this->newsRepository->fetchFeatured($query->limit);
            }
        );

        $authorIds   = array_map(static fn(News $newsItem) => $newsItem->getAuthorId(), $newsItems);
        $categoryIds = array_map(static fn(News $newsItem) => $newsItem->getCategoryId(), $newsItems);

        $authors    = $this->userRepository->findByIds($authorIds);
        $categories = $this->categoryRepository->findByIds($categoryIds);

        $newsDTOs = array_map(function (News $news) use ($authors, $categories) {
            return new NewsDTO(
                id:          $news->getId(),
                title:       $news->getTitle(),
                slug:        $news->getSlug(),
                content:     $news->getContent(),
                thumbnail:   $this->thumbnailService->getPublicUrl($news->getThumbnail()),
                publishedAt: $news->getPublishedAt() ? new \DateTimeImmutable($news->getPublishedAt()->toDateTimeString()) : null,
                createdAt:   $news->getCreatedAt() ? new \DateTimeImmutable($news->getCreatedAt()->toDateTimeString()) : null,
                excerpt:     $news->getExcerpt(),
                active:      $news->getActive(),
                featured:    $news->getFeatured(),
                views:       $news->getViews(),
                updatedAt:   $news->getUpdatedAt() ? new \DateTimeImmutable($news->getUpdatedAt()->toDateTimeString()) : null,
                author:      isset($authors[$news->getAuthorId()]) ? new AuthorDTO(
                                 id:    $authors[$news->getAuthorId()]->getId(),
                                 name:  $authors[$news->getAuthorId()]->getName(),
                                 email: $authors[$news->getAuthorId()]->getEmail(),
                             ) : null,
                category:    isset($categories[$news->getCategoryId()]) ? new CategoryDTO(
                                 id:   $categories[$news->getCategoryId()]->getId(),
                                 name: $categories[$news->getCategoryId()]->getName(),
                                 slug: $categories[$news->getCategoryId()]->getSlug(),
                             ) : null,
            );
        }, $newsItems);

        return new ResultDTO($newsDTOs);
    }
}
