<?php

namespace App\Policies;

use App\Application\Core\News\Repositories\NewsRepositoryInterface;
use App\Application\Core\Role\Enums\Role as RoleEnum;
use App\Models\News;
use App\Models\User;


class NewsPolicy
{

    public function __construct(private NewsRepositoryInterface $newsRepository)
    {
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $user->hasPermission('view_news');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, int|string|News $newsOrId): bool
    {
        return $this->isAdmin($user) || $user->hasPermission('view_news');
    }
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $user->hasPermission('create_news');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, int|string|News $newsOrId): bool
    {
        $news = $this->getNewsFromParam($newsOrId);

        return $this->isAdmin($user) || ($user->hasPermission('update_news') && $news && ($user->getId() === $news->getAuthorId()));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, int|string|News $newsOrId): bool
    {
        $news = $this->getNewsFromParam($newsOrId);

        return $this->isAdmin($user) || ($user->hasPermission('delete_news') && $news && ($user->getId() === $news->getAuthorId()));
    }

    /**
     * @param int|string|News $newsOrId
     *
     * @return News|null
     */
    private function getNewsFromParam(int|string|News $newsOrId): ?News {
        if (!($newsOrId instanceof News)) {
            return $this->newsRepository->find($newsOrId);
        } else {
            return $newsOrId;
        }
    }

    private function isAdmin(User $user): bool
    {
        return $user->hasRole(RoleEnum::ADMIN->value);
    }
}
