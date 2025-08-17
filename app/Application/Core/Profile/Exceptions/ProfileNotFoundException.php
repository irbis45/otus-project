<?php

declare(strict_types=1);

namespace App\Application\Core\Profile\Exceptions;

use Exception;

final class ProfileNotFoundException extends Exception
{
    public function __construct(string $message = "Профиль не найден")
    {
        parent::__construct($message);
    }
}
