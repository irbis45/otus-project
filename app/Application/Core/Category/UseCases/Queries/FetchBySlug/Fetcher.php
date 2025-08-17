<?php

namespace App\Application\Core\Category\UseCases\Queries\FetchBySlug;

use App\Application\Core\Category\DTO\CategoryDTO;
use App\Application\Core\Category\Exceptions\CategoryNotFoundException;
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
     * @return CategoryDTO
     * @throws CategoryNotFoundException
     */
    public function fetch(Query $query): CategoryDTO
    {
        /** @var ?Category $category */
        $category = $this->categoryRepository->findBySlug($query->slug);

        if (!$category) {
            throw new CategoryNotFoundException('Категория не найдена');
        }

        return new CategoryDTO(
            id: $category->getId(),
            name: $category->getName(),
            description: $category->getDescription(),
            slug: $category->getSlug(),
            active: $category->getActive(),
            newsCount: null,
        );
    }
}
