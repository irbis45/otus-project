<?php

namespace App\Application\Core\Category\UseCases\Queries\FetchAllPagination;

use App\Application\Core\Category\DTO\CategoryDTO;
use App\Application\Core\Category\DTO\PaginatedResult;
use App\Application\Core\Category\Repositories\CategoryRepositoryInterface;
use App\Models\Category;

class Fetcher
{
    /**
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {
    }

    /**
     * @param Query $query
     *
     * @return PaginatedResult
     */
    public function fetch(Query $query): PaginatedResult
    {
        $categories = $this->categoryRepository->fetchPaginated($query->limit, $query->offset, $query->onlyActive);
        $total = $this->categoryRepository->count($query->onlyActive);

        $categoryDTOs = array_map(function (Category $category) {
            return new CategoryDTO(
                id: $category->getId(),
                name: $category->getName(),
                description: $category->getDescription(),
                slug: $category->getSlug(),
                active: $category->getActive(),
                newsCount: null,
            );
        }, $categories);

        return new PaginatedResult(
            items: $categoryDTOs,
            total: $total,
            limit: $query->limit,
            offset: $query->offset
        );
    }
}
