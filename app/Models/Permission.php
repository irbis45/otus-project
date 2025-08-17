<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'slug',
        'name',
    ];

    /** @var bool  */
    public $timestamps = false;

    protected $columnMap = [
        'id' => 'id',
        'name' => 'name',
        'slug' => 'slug',
    ];

    public function getColumnName($property)
    {
        return $this->columnMap[$property] ?? $property;
    }

    public function getId(): int
    {
        return $this->{$this->getColumnName('id')};
    }

    public function getSlug(): string
    {
        return $this->{$this->getColumnName('slug')};
    }

    public function getName(): string
    {
        return $this->{$this->getColumnName('name')};
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role');
    }
}
