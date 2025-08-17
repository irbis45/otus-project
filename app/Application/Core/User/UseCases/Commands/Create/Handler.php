<?php

namespace App\Application\Core\User\UseCases\Commands\Create;

use App\Application\Contracts\PasswordHasherInterface;
use App\Application\Core\Role\Enums\Role;
use App\Application\Core\Role\Repositories\RoleRepositoryInterface;
use App\Application\Core\User\DTO\UserDTO;
use App\Application\Core\User\Exceptions\UserEmailAlreadyExistsException;
use App\Application\Core\User\Exceptions\UserSaveException;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Models\User;
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
        if ($this->userRepository->existsByEmail($command->email)) {
            throw new UserEmailAlreadyExistsException($command->email);
        }

        $user = new User();

        $user->{$user->getColumnName('name')} = $command->name;
        $user->{$user->getColumnName('email')} = $command->email;

        if ($command->password) {
            $user->{$user->getColumnName('password')} = $this->passwordHasher->hash($command->password);
        }

        $result = $this->userRepository->save($user);

        if (!$result) {
            throw new UserSaveException("Не удалось сохранить пользователя '{$command->name}'");
        }

       //$rolesToAssign = $command->roles ?: [Role::USER->value];

        // Ищем роли по слагам из команды
       // $roles = $this->roleRepository->findBySlugs($rolesToAssign);

//        if (!empty($roles)) {
//            $user->attachRoles(array_map(fn($role) => $role->id, $roles));
//        }

        return new UserDTO(
            id:              $user->getId(),
            name:            $user->getName(),
            email:           $user->getEmail(),
            createdAt:       $user->getCreatedAt() ? new DateTimeImmutable($user->getCreatedAt()) : null,
            emailVerifiedAt: $user->getEmailVerifiedAt()? new DateTimeImmutable($user->getEmailVerifiedAt()) : null,
            updatedAt:       $user->getUpdatedAt() ? new DateTimeImmutable($user->getUpdatedAt()) : null,
            //roles:           array_map(fn($role) => $role->id, $roles),
        );
    }
}
