<?php

namespace App\Application\Core\User\UseCases\Queries\FetchAll;

use App\Application\Core\Role\Repositories\RoleRepositoryInterface;
use App\Application\Core\User\DTO\ResultDTO;
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
        private RoleRepositoryInterface $roleRepository,
    ) {
    }

    /**
     *
     * @return ResultDTO
     */
    public function fetch(): ResultDTO
    {
        $users = $this->userRepository->fetchAll();

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

        return new ResultDTO($userDTOs);
    }
}
