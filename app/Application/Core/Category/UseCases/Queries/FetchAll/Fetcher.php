<?php

namespace App\Application\Core\Category\UseCases\Queries\FetchAll;

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
        private CategoryRepositoryInterface $categoryRepository
    ) {
    }

    /**
     *
     * @return ResultDTO
     */
    public function fetch(): ResultDTO
    {
        $categories = $this->categoryRepository->fetchAll();

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

        return new ResultDTO($categoryDTOs);
    }
}
