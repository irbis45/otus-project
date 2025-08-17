<?php

namespace App\Application\Core\User\UseCases\Commands\Delete;

use App\Application\Core\User\Exceptions\UserNotFoundException;
use App\Application\Core\User\Repositories\UserRepositoryInterface;

class Handler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function handle(Command $command): bool
    {
        $user = $this->userRepository->find($command->id);

        if (!$user) {
            throw new UserNotFoundException('Пользователь не найден');
        }

        return $this->userRepository->delete($user);
    }
}
