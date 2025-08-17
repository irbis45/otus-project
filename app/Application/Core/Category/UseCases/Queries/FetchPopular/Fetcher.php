<?php

namespace App\Application\Core\Category\UseCases\Queries\FetchPopular;

use App\Application\Contracts\CacheInterface;
use App\Application\Core\Category\DTO\CategoryDTO;
use App\Application\Core\Category\DTO\ResultDTO;
use App\Application\Core\Category\Repositories\CategoryRepositoryInterface;
use App\Models\Category;

class Fetcher
{
    /**
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
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
        $categories = $this->cache->rememberWithTags(['categories', 'news_count'], 'popular_categories_list', config('cache.category.popular'),
            function () use ($query) {
                return $this->categoryRepository->getPopular($query->limit);
            }
        );

        $categoryDTOs = array_map(function (Category $category) {
            return new CategoryDTO(
                id: $category->getId(),
                name: $category->getName(),
                description: $category->getDescription(),
                slug: $category->getSlug(),
                active: $category->getActive(),
                newsCount: $category->news_count ?? null,
            );
        }, $categories);

        return new ResultDTO($categoryDTOs);
    }
}
