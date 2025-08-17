<?php

declare(strict_types=1);

namespace App\Infrastructure\Eloquent\Repositories\Roles;

use App\Application\Core\Role\Repositories\RoleRepositoryInterface;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RoleRepository implements RoleRepositoryInterface
{
    public function fetchAll(): array {
        return Role::all()->all();
    }

    public function getByUserIds(array $userIds): array
    {
      /*  return User::with('roles:slug')
                   ->whereIn('id', $userIds)
                   ->get()
                   ->mapWithKeys(fn($user) => [$user->getId() => $user->roles->pluck('slug')->toArray()])
                   ->toArray();*/

        return DB::table('role_user')
                 ->join('roles', 'role_user.role_id', '=', 'roles.id')
                 ->whereIn('role_user.user_id', $userIds)
                 ->select('role_user.user_id', 'roles.slug')
                 ->get()
                 ->groupBy('user_id')
                 ->map(fn($roles) => $roles->pluck('slug')->toArray())
                 ->toArray();
    }

    public function getByUserId(int $userId): array
    {
       /* return User::with('roles:slug')
                   ->find($userId)?->roles->pluck('slug')->toArray() ?? [];*/
        return DB::table('role_user')
                 ->join('roles', 'role_user.role_id', '=', 'roles.id')
                 ->where('role_user.user_id', $userId)
                 ->select('roles.slug')
                 ->get()
                 ->pluck('slug')
                 ->toArray();
    }

    public function findBySlug(string $slug): ?Role
    {
        return Role::where('slug', $slug)->first();
    }

    public function findBySlugs(array $slugs): array
    {
        return Role::whereIn('slug', $slugs)->get()->all();
    }
}
