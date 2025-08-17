<?php

namespace App\Application\Core\Profile\UserCases\Commands\Update;

use App\Application\Contracts\PasswordHasherInterface;
use App\Application\Core\Profile\DTO\ProfileDTO;
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

    public function handle(Command $command): ProfileDTO
    {
        $user = $this->userRepository->find($command->id);

        if ( ! $user) {
            throw new ProfileNotFoundException('Профиль не найден');
        }

        $user->{$user->getColumnName('name')}  = $command->name;
        $user->{$user->getColumnName('email')} = $command->email;

        if ($command->password !== null) {
            if ($command->current_password === null || !$this->passwordHasher->check($command->current_password, $user->password)) {
                throw new ProfileInvalidCurrentPasswordException();
            }

            $user->{$user->getColumnName('password')} = $this->passwordHasher->hash($command->password);
        }

        $this->userRepository->save($user);

        return new ProfileDTO(
            id:              $user->getId(),
            name:            $user->getName(),
            email:           $user->getEmail(),
        );
    }
}
