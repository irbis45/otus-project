<?php

declare(strict_types=1);

namespace App\Application\Core\Permission\Enums;

enum Permission: string
{
    case VIEW_ADMIN_PANEL = 'view_admin_panel';
    case CREATE_NEWS = 'create_news';
    case EDIT_NEWS = 'edit_news';
    case DELETE_NEWS = 'delete_news';
}
