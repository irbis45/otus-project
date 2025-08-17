<?php

namespace App\Application\Core\Profile\UserCases\Commands\Delete;

use App\Application\Contracts\PasswordHasherInterface;
use App\Application\Core\Profile\Exceptions\ProfileInvalidCurrentPasswordException;
use App\Application\Core\Profile\Exceptions\ProfileNotFoundException;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
class Handler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
    ) {
    }

    /**
     * @param Command $command
     *
     * @return bool|null
     * @throws ProfileInvalidCurrentPasswordException
     * @throws ProfileNotFoundException
     */
    public function handle(Command $command): ?bool
    {
        $user = $this->userRepository->find($command->id);

        if ( ! $user) {
            throw new ProfileNotFoundException();
        }

        if (! $this->passwordHasher->check($command->password, $user->password)) {
            throw new ProfileInvalidCurrentPasswordException();
        }

        return $this->userRepository->delete($user);
    }
}
