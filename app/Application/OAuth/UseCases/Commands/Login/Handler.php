<?php

declare(strict_types=1);

namespace App\Application\OAuth\UseCases\Commands\Login;

use App\Application\OAuth\Contracts\AuthServiceInterface;
use App\Application\OAuth\Exceptions\InvalidCredentialsException;

class Handler
{
    public function __construct(
        private AuthServiceInterface $authService,
    ) {}

    public function handle(Command $command): array
    {
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
