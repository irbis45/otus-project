<?php

declare(strict_types=1);

namespace App\Application\Contracts;

interface ViewedNewsStorageInterface
{
    public function has(int $newsId): bool;
    public function add(int $newsId): void;
}
