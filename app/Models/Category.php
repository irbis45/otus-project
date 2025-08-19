<?php

namespace App\Models;

use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class Category extends BaseModel
{
    use HasFactory, HasSlug;

    /** @var bool  */
    public $timestamps = false;

    /** @var string[]  */
    protected $fillable = [
        'name',
        'description',
        'active',
    ];

    protected $columnMap = [
        'id' => 'id',
        'name' => 'name',
        'description' => 'description',
        'slug' => 'slug',
        'active' => 'active',
    ];

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

    public function getDescription(): ?string
    {
        return $this->{$this->getColumnName('description')};
    }

    public function getSlug(): string
    {
        return $this->{$this->getColumnName('slug')};
    }

    public function getActive(): bool
    {
        return $this->{$this->getColumnName('active')};
    }

    public static function slugFrom(): string
    {
        return 'name';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    /**
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();
    }

    /**
     * @return HasMany
     */
    public function news(): HasMany
    {
        return $this->hasMany(News::class);
    }


    public function scopePublished(Builder $query): Builder
    {
        return $query->where('active', true)
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', Carbon::now());
    }

    /**
     * @return HasMany
     */
    public function publishedNews(): HasMany
    {
        return $this->news()->published();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}
