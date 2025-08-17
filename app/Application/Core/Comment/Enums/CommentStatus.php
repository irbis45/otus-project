<?php

declare(strict_types=1);

namespace App\Application\Core\Comment\Enums;

enum CommentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'В ожидании',
            self::Approved => 'Одобрено',
            self::Rejected => 'Отклонено',
        };
    }
}
