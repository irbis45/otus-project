<?php

declare(strict_types=1);

namespace App\Application\Core\User\UseCases\Queries\SearchUsers;

use App\Application\Core\Role\Repositories\RoleRepositoryInterface;
use App\Application\Core\User\DTO\PaginatedResult;
use App\Application\Core\User\DTO\UserDTO;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Models\User;
use DateTimeImmutable;

class Fetcher
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RoleRepositoryInterface $roleRepository
    ) {
    }

    public function fetch(Query $query): PaginatedResult
    {
        $users = $this->userRepository->searchPaginated(
            $query->limit,
            $query->offset,
            $query->search
        );

        $total = $this->userRepository->searchCount($query->search);

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
                createdAt:       $user->getCreatedAt() ? new DateTimeImmutable($user->getCreatedAt()->toDateTimeString()) : null,
                emailVerifiedAt: $user->getEmailVerifiedAt() ? new DateTimeImmutable($user->getEmailVerifiedAt()->toDateTimeString()) : null,
                updatedAt:       $user->getUpdatedAt() ? new DateTimeImmutable($user->getUpdatedAt()->toDateTimeString()) : null,
                roles:           isset($rolesByUser[$user->getId()]) ? $rolesByUser[$user->getId()] : [],
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
