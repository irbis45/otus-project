<?php

namespace App\Policies;

use App\Application\Core\Comment\Repositories\CommentRepositoryInterface;
use App\Models\Comment;
use App\Models\User;
use App\Application\Core\Role\Enums\Role as RoleEnum;
use Illuminate\Auth\Access\Response;

class CommentPolicy
{

    public function __construct(private CommentRepositoryInterface $commentRepository)
    {
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $user->hasPermission('view_comments');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, int|string|Comment $commentOrId): bool
    {
        return $this->isAdmin($user) || $user->hasPermission('view_comments');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $user->hasPermission('create_comments');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, int|string|Comment $commentOrId): bool
    {
        return $this->isAdmin($user) || $user->hasPermission('update_comments');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, int|string|Comment $commentOrId): bool
    {
        return $this->isAdmin($user) || $user->hasPermission('delete_comments');
    }

    /**
     * @param int|string|Comment $commentOrId
     *
     * @return Comment|null
     */
    private function getCommentFromParam(int|string|Comment $commentOrId): ?comment {
        if (!($commentOrId instanceof Comment)) {
            return $this->commentRepository->find($commentOrId);
        } else {
            return $commentOrId;
        }
    }

    private function isAdmin(User $user): bool
    {
        return $user->hasRole(RoleEnum::ADMIN->value);
    }
}
