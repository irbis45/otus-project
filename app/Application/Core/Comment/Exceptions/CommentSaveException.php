<?php

declare(strict_types=1);

namespace App\Application\Core\Comment\Exceptions;

use Exception;

final class CommentSaveException extends Exception
{
    public function __construct(string $message = "Не удалось сохранить комментарий")
    {
        parent::__construct($message);
    }
}
