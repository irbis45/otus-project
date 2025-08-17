<?php

declare(strict_types=1);

namespace App\Application\OAuth\UseCases\Commands\Register;

use App\Application\Core\Role\Enums\Role;
use App\Application\Core\Role\Repositories\RoleRepositoryInterface;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Application\OAuth\Exceptions\UserSaveException;
use App\Infrastructure\PasswordHasher\LaravelPasswordHasher;
use App\Models\User;

/**
 * Не используется, так как регистрация пользователя через API не предусмотрена.
 */
class Handler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RoleRepositoryInterface $roleRepository,
        private LaravelPasswordHasher $passwordHasher,
    ) {}

    public function handle(Command $command): array
    {
        $user = new User();
        $user->{$user->getColumnName('name')} = $command->name;
        $user->{$user->getColumnName('email')} = $command->email;
        $user->{$user->getColumnName('password')} = $this->passwordHasher->hash($command->password);

        $result = $this->userRepository->save($user);

        if (!$result) {
            throw new UserSaveException("Не удалось сохранить токен");
        }

        $roles = $this->roleRepository->findBySlugs([Role::USER->value]);

        if (!empty($roles)) {
            $user->attachRoles(array_map(fn($role) => $role->id, $roles));
        }

        $token = $user->createToken('authToken')->accessToken;

        return ['token' => $token];
    }
}
