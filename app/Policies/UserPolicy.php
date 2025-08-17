<?php

namespace App\Policies;

use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Application\Core\Role\Enums\Role as RoleEnum;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $user->hasPermission('view_users');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, int|string|User $userOrId): bool
    {
        return $this->isAdmin($user) || $user->hasPermission('view_users');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $user->hasPermission('create_users');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, int|string|User $userOrId): bool
    {
        return $this->isAdmin($user) || $user->hasPermission('update_users');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, int|string|User $userOrId): bool
    {
        return $this->isAdmin($user) || $user->hasPermission('delete_users');
    }

    /**
     * @param int|string|User $userOrId
     *
     * @return User|null
     */
    private function getUserFromParam(int|string|User $userOrId): ?User {
        if (!($userOrId instanceof User)) {
            return $this->userRepository->find($userOrId);
        } else {
            return $userOrId;
        }
    }

    private function isAdmin(User $user): bool
    {
        return $user->hasRole(RoleEnum::ADMIN->value);
    }
}
