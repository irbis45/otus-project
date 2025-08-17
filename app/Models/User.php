<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Application\Core\Permission\Enums\Permission as PermissionEnum;
use App\Application\Core\Role\Enums\Role as RoleEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Passport\HasApiTokens;

//use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $columnMap = [
        'id' => 'id',
        'name' => 'name',
        'email' => 'email',
        'password' => 'password',
        'email_verified_at' => 'email_verified_at',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
    ];

    protected ?Collection $cachedPermissions = null;

    protected static ?Role $defaultRole = null;
    public function getColumnName($property)
    {
        return $this->columnMap[$property] ?? $property;
    }

    public function getId(): int
    {
        return $this->{$this->getColumnName('id')};
    }

    public function getName(): string
    {
        return $this->{$this->getColumnName('name')};
    }

    public function getEmail(): string
    {
        return $this->{$this->getColumnName('email')};
    }

    public function getEmailVerifiedAt(): ?Carbon
    {
        return $this->{$this->getColumnName('email_verified_at')};
    }


    public function getCreatedAt(): ?Carbon {
        return $this->{$this->getColumnName('created_at')};
    }

    public function getUpdatedAt(): ?Carbon {
        return $this->{$this->getColumnName('updated_at')};
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*protected static function booted()
    {
        static::created(function ($user) {
            $role = Role::where('slug', 'user')->first();
            if ($role) {
                $user->roles()->attach($role->id);
            }
            //$user->roles()->attach(RoleEnum::USER->value);
        });
    }*/
   /* protected static function booted()
    {
        static::created(function ($user) {
            if (!self::$defaultRole) {
                self::$defaultRole = Role::where('slug', RoleEnum::USER->value)->first();
            }

            if (self::$defaultRole && !$user->roles()->where('slug', RoleEnum::USER->value)->exists()) {
                $user->roles()->attach(self::$defaultRole->id);
            }
        });
    }*/

    /**
     * @return HasMany
     */
    public function news(): HasMany
    {
        return $this->hasMany(News::class, 'author_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'author_id', 'id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(RoleEnum|string ...$roles): bool
    {
        foreach ($roles as $role) {
            $roleSlug = $role instanceof RoleEnum ? $role->value : $role;
            if ($this->roles->contains('slug', $roleSlug)) {
                return true;
            }
        }

        return false;
    }

    // Связь прав через роли (permissions через роли)
   /* public function permissions(): BelongsToMany
    {
        return $this->roles()->with('permissions')->get()->pluck('permissions')->flatten()->unique('id');
    }*/

    /**
     * @return Collection
     */
    public function permissions(): Collection
    {
        if ($this->cachedPermissions !== null) {
            return $this->cachedPermissions;
        }

        if (!$this->relationLoaded('roles')) {
            $this->load('roles.permissions');
        }

        $this->cachedPermissions = $this->roles
            ->flatMap(fn ($role) => $role->permissions->pluck('slug'))
            ->unique()
            ->values();

        return $this->cachedPermissions;
    }

    public function hasPermission(string|PermissionEnum $permission): bool
    {
        $permissionSlug = is_string($permission) ? $permission : $permission->value;

        if ($this->hasRole(RoleEnum::ADMIN->value)) {
            return true;
        }

        return $this->permissions()->contains($permissionSlug);
    }

    public function attachRoles(array $roleIds): self
    {
        // Убираем дубликаты ID, на всякий случай
        $roleIds = array_unique($roleIds);

        if (!empty($roleIds)) {
            // Добавляем новые роли, не удаляя существующие
            $this->roles()->syncWithoutDetaching($roleIds);
        }

        return $this;
    }

    public function syncRoles(array $roleIds): self
    {
        // Убираем дубликаты, на всякий случай
        $roleIds = array_unique($roleIds);

        // Синхронизируем роли, удаляя все, которых нет в $roleIds, и добавляя новые
        $this->roles()->sync($roleIds);

        return $this;
    }
}
