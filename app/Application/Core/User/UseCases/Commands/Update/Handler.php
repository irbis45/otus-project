<?php

namespace App\Application\Core\User\UseCases\Commands\Update;

use App\Application\Contracts\PasswordHasherInterface;
use App\Application\Core\Role\Repositories\RoleRepositoryInterface;
use App\Application\Core\User\DTO\UserDTO;
use App\Application\Core\User\Exceptions\UserNotFoundException;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use DateTimeImmutable;

class Handler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
        private RoleRepositoryInterface $roleRepository,
    ) {
    }

    public function handle(Command $command): UserDTO
    {
        $user = $this->userRepository->find($command->id);

        if ( ! $user) {
            throw new UserNotFoundException('Пользователь не найден');
        }

        $user->{$user->getColumnName('name')}  = $command->name;
        $user->{$user->getColumnName('email')} = $command->email;

        if ($command->password) {
            $user->{$user->getColumnName('password')} = $this->passwordHasher->hash($command->password);
        }

        $this->userRepository->save($user);

        $roles = $this->roleRepository->getByUserId($user->getId());

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
