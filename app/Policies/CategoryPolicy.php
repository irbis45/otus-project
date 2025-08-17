<?php

namespace App\Policies;

use App\Application\Core\Role\Enums\Role as RoleEnum;
use App\Application\Core\Category\Repositories\CategoryRepositoryInterface;
use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{

    public function __construct(private CategoryRepositoryInterface $categoryRepository)
    {
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $user->hasPermission('view_categories');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, int|string|Category $categoryOrId): bool
    {
        return $this->isAdmin($user) || $user->hasPermission('view_categories');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $user->hasPermission('create_categories');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, int|string|Category $categoryOrId): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, int|string|Category $categoryOrId): bool
    {
        return $this->isAdmin($user);
    }


    /**
     * @param int|string|Category $categoryOrId
     *
     * @return Category|null
     */
    private function getCategoryFromParam(int|string|Category $categoryOrId): ?Category {
        if (!($categoryOrId instanceof Category)) {
            return $this->categoryRepository->find($categoryOrId);
        } else {
            return $categoryOrId;
        }
    }
    private function isAdmin(User $user): bool
    {
        return $user->hasRole(RoleEnum::ADMIN->value);
    }
}
