<?php

declare(strict_types=1);

namespace App\Application\OAuth\Exceptions;

use Exception;

class UserNotAuthorizedException extends Exception
{
    protected $message = 'Доступ запрещён: недостаточно прав';
    protected $code = 403;
}
