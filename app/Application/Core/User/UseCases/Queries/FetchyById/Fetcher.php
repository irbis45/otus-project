<?php

namespace App\Application\Core\User\UseCases\Queries\FetchyById;

use App\Application\Core\Role\Repositories\RoleRepositoryInterface;
use App\Application\Core\User\DTO\UserDTO;
use App\Application\Core\User\Exceptions\UserNotFoundException;
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
     * @param Query $query
     *
     * @return UserDTO
     * @throws UserNotFoundException
     */
    public function fetch(Query $query): UserDTO
    {
        /** @var ?User $user */
        $user = $this->userRepository->find($query->id);

        if (!$user) {
            throw new UserNotFoundException('Пользователь не найден');
        }

        $roles = $this->roleRepository->getByUserId($user->getId()) ?? [];

        return new UserDTO(
            id:              $user->getId(),
            name:            $user->getName(),
            email:           $user->getEmail(),
            createdAt:       $user->getCreatedAt() ? new DateTimeImmutable($user->getCreatedAt()->toDateTimeString()) : null,
            emailVerifiedAt: $user->getEmailVerifiedAt() ? new DateTimeImmutable($user->getEmailVerifiedAt()->toDateTimeString()) : null,
            updatedAt:       $user->getUpdatedAt() ? new DateTimeImmutable($user->getUpdatedAt()->toDateTimeString()) : null,
            roles:           $roles,
        );
    }
}
