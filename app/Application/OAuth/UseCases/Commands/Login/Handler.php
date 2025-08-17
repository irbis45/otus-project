<?php

declare(strict_types=1);

namespace App\Application\OAuth\UseCases\Commands\Login;

use App\Application\OAuth\Exceptions\UserNotAuthorizedException;
use App\Application\OAuth\Exceptions\UserNotFoundException;
use App\Application\OAuth\Contracts\AuthServiceInterface;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Application\OAuth\Exceptions\InvalidCredentialsException;
use App\Application\Core\Role\Enums\Role as RoleEnum;

class Handler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AuthServiceInterface $authService,
    ) {}

    public function handle(Command $command): array
    {
        $user = $this->userRepository->findByEmail($command->email);

        if ( ! $user) {
            throw new UserNotFoundException('Пользователь не найден');
        }

        if (!$user->hasRole(RoleEnum::ADMIN->value) && !$user->hasRole(RoleEnum::EDITOR->value)) {
            throw new UserNotAuthorizedException();
        }

        $user = $this->authService->attempt($command->email, $command->password);
        if (is_null($user)) {
            throw new InvalidCredentialsException();
        }

        $tokenResult = $user->createToken('authToken');

        return [
            'token' => $tokenResult->accessToken,
        ];
    }
}
