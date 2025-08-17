<?php

namespace App\Application\Core\Category\UseCases\Commands\Create;

use App\Application\Core\Category\DTO\CategoryDTO;
use App\Application\Core\Category\Exceptions\CategoryAlreadyExistsException;
use App\Application\Core\Category\Exceptions\CategorySaveException;
use App\Application\Core\Category\Repositories\CategoryRepositoryInterface;
use App\Models\Category;

class Handler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {
    }

    public function handle(Command $command): CategoryDTO
    {
        if ($this->categoryRepository->existsByName($command->name)) {
            throw new CategoryAlreadyExistsException($command->name);
        }

        $category = new Category();

        $category->{$category->getColumnName('name')} = $command->name;
        $category->{$category->getColumnName('description')} = $command->description;
        $category->{$category->getColumnName('active')} = $command->active;

        $result = $this->categoryRepository->save($category);

        if (!$result) {
            throw new CategorySaveException("Не удалось сохранить категорию '{$command->name}'");
        }

        return new CategoryDTO(
            id: $category->getId(),
            name: $category->getName(),
            description: $category->getDescription(),
            slug: $category->getSlug(),
            active: $category->getActive(),
        );
    }
}
