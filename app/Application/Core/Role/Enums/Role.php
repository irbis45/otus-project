<?php

declare(strict_types=1);

namespace App\Application\Core\Role\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case USER = 'user';
    case USER_WITHOUT_COMMENTS = 'user_without_comments';
}
