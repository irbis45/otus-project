<?php

declare(strict_types=1);

namespace App\Application\OAuth\Contracts;

interface OAuthRefreshTokenRepositoryInterface
{
    public function revokeRefreshTokensByAccessTokenId(string $tokenId): void;
}
