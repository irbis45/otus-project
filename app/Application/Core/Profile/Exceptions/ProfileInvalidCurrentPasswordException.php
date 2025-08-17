<?php

declare(strict_types=1);

namespace App\Application\Core\Profile\Exceptions;

use Exception;

final class ProfileInvalidCurrentPasswordException extends Exception
{
    public function __construct(string $message = "Текущий пароль указан неверно")
    {
        parent::__construct($message);
    }
}
