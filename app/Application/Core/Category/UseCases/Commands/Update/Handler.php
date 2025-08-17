<?php

namespace App\Application\Core\Category\UseCases\Commands\Update;

use App\Application\Core\Category\DTO\CategoryDTO;
use App\Application\Core\Category\Exceptions\CategoryAlreadyExistsException;
use App\Application\Core\Category\Exceptions\CategoryNotFoundException;
use App\Application\Core\Category\Repositories\CategoryRepositoryInterface;

class Handler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {
    }

    public function handle(Command $command): CategoryDTO
    {
        $category = $this->categoryRepository->find($command->id);

        if (!$category) {
            throw new CategoryNotFoundException('Категория не найдена');
        }

        if ($category->getName() !== $command->name &&
            $this->categoryRepository->existsByName($command->name)) {
            throw new CategoryAlreadyExistsException($command->name);
        }

        $category->{$category->getColumnName('name')}  = $command->name;
        $category->{$category->getColumnName('description')} = $command->description;
        $category->{$category->getColumnName('active')} = $command->active;

        $this->categoryRepository->save($category);

        return new CategoryDTO(
            id: $category->getId(),
            name: $category->getName(),
            description: $category->getDescription(),
            slug: $category->getSlug(),
            active: $category->getActive(),
        );
    }
}
