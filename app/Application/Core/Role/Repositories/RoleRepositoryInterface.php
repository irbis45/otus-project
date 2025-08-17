<?php

declare(strict_types=1);

namespace App\Application\Core\Role\Repositories;

use App\Models\Role;

interface RoleRepositoryInterface
{
    public function fetchAll(): array;
    public function getByUserIds(array $userIds): array;

    public function getByUserId(int $userId): array;

    public function findBySlug(string $slug): ?Role;

    public function findBySlugs(array $slugs): array;
}
