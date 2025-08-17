<?php

namespace App\Application\Core\Category\UseCases\Commands\Delete;

use App\Application\Core\Category\Exceptions\CategoryNotFoundException;
use App\Application\Core\Category\Repositories\CategoryRepositoryInterface;

class Handler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {
    }

    public function handle(Command $command): bool
    {
        $category = $this->categoryRepository->find($command->id);

        if (!$category) {
            throw new CategoryNotFoundException('Категория не найдена');
        }

        return $this->categoryRepository->delete($category);
    }
}
