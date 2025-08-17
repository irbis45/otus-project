<?php

namespace App\Application\Core\News\Exceptions;

use Exception;

final class NewsSaveException extends Exception
{
    public function __construct(string $message = "Не удалось сохранить новость")
    {
        parent::__construct($message);
    }
}
