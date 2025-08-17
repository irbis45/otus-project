<?php

declare(strict_types=1);

namespace App\Application\Core\Role\UseCases\Commands\ChangeUserRole;
use App\Application\Core\Role\Repositories\RoleRepositoryInterface;
use App\Application\Core\User\Repositories\UserRepositoryInterface;

class Handler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RoleRepositoryInterface $roleRepository,
    ) {}

    public function handle(Command $command): void
    {
        $user = $this->userRepository->find($command->userId);
        $roles = $this->roleRepository->findBySlugs($command->roleSlugs);
        $roleIds = array_map(fn($role) => $role->id, $roles);

        $user->syncRoles($roleIds);
    }
}
