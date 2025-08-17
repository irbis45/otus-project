<?php

declare(strict_types=1);

namespace App\Application\OAuth\Contracts;

interface OAuthTokenRepositoryInterface
{
    public function revokeAccessToken(string $tokenId): void;
}
