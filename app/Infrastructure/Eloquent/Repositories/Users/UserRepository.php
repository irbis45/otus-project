<?php

declare(strict_types=1);

namespace App\Infrastructure\Eloquent\Repositories\Users;

use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    /**
     * @return User[]
     */
    public function fetchAll(): array {
        return User::all()->all();
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function fetchPaginated(int $limit, int $offset): array {
        return User::query()
                       ->orderBy('id', 'desc')
                       ->limit($limit)
                       ->offset($offset)
                       ->get()
                       ->all();
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return User::query()
                   ->count();
    }

    /**
     * @param int $id
     *
     * @return User|null
     */
    public function find(int $id): ?User {
        return User::query()->find($id);
    }


    /**
     * @param User $user
     *
     * @return bool
     */
    public function save(User $user): bool {
        return $user->save();
    }

    /**
     * @param User $user
     *
     * @return bool|null
     */
    public function delete(User $user): ?bool {
        return $user->delete();
    }

    /**
     * @param string $email
     * @return bool
     */
    public function existsByEmail(string $email): bool
    {
        return User::query()->where('email', $email)->exists();
    }

    /**
     * @param User[] $ids
     *
     * @return array
     */
    public function findByIds(array $ids): array
    {
        return User::query()->whereIn('id', $ids)->get()->keyBy('id')->all();
    }
}
