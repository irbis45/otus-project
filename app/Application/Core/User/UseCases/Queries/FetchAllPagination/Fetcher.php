<?php

namespace App\Application\Core\User\UseCases\Queries\FetchAllPagination;

use App\Application\Core\Role\Repositories\RoleRepositoryInterface;
use App\Application\Core\User\DTO\PaginatedResult;
use App\Application\Core\User\DTO\UserDTO;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Models\User;
use DateTimeImmutable;

class Fetcher
{
    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RoleRepositoryInterface $roleRepository
    ) {
    }

    /**
     * @param Query $query
     *
     * @return PaginatedResult
     */
    public function fetch(Query $query): PaginatedResult
    {
        $users = $this->userRepository->fetchPaginated($query->limit, $query->offset);
        $total = $this->userRepository->count();

        $userIds = array_map(fn(User $user) => $user->getId(), $users);

        $rolesByUser = [];
        if (!empty($userIds)) {
            $rolesByUser = $this->roleRepository->getByUserIds($userIds);
        }

        $userDTOs = array_map(function (User $user) use ($rolesByUser) {

            return new UserDTO(
                id:              $user->getId(),
                name:            $user->getName(),
                email:           $user->getEmail(),
                createdAt:       $user->getCreatedAt() ? new DateTimeImmutable($user->getCreatedAt()) : null,
                emailVerifiedAt: $user->getEmailVerifiedAt()? new DateTimeImmutable($user->getEmailVerifiedAt()) : null,
                updatedAt:       $user->getUpdatedAt() ? new DateTimeImmutable($user->getUpdatedAt()) : null,
                roles:           isset($rolesByUser[$user->getId()]) ? $rolesByUser[$user->getId()] : [],
                // permissions: method_exists($user, 'getPermissions') ? $user->getPermissions()->permissions : [],
            );
        }, $users);

        return new PaginatedResult(
            items: $userDTOs,
            total: $total,
            limit: $query->limit,
            offset: $query->offset
        );
    }
}
