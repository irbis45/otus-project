<?php

declare(strict_types=1);

namespace App\Application\OAuth\UseCases\Commands\Logout;

use App\Application\OAuth\Contracts\OAuthRefreshTokenRepositoryInterface;
use App\Application\OAuth\Contracts\OAuthTokenRepositoryInterface;

class Handler
{
    public function __construct(
        private OAuthTokenRepositoryInterface $tokenRepository,
        private OAuthRefreshTokenRepositoryInterface $refreshTokenRepository
    )
    {
    }

    public function handle(Command $command): void
    {
        $this->tokenRepository->revokeAccessToken($command->tokenId);
        $this->refreshTokenRepository->revokeRefreshTokensByAccessTokenId($command->tokenId);
    }
}
