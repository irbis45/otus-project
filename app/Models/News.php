<?php

namespace App\Models;

use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class News extends BaseModel
{
    use HasFactory;
    use HasSlug;


    /** @var string[]  */
    protected $fillable = [
        'title',
        'content',
        'thumbnail',
        'active',
        'excerpt',
        'featured',
        'published_at',
        'views',
        'author_id',
        'category_id',
    ];


    protected $columnMap = [
        'id' => 'id',
        'title' => 'title',
        'thumbnail' => 'thumbnail',
        'excerpt' => 'excerpt',
        'content' => 'content',
        'publishedAt' => 'published_at',
        'active' => 'active',
        'author_id' => 'author_id',
        'featured' => 'featured',
        'views' => 'views',
        'category_id' => 'category_id',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
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

    public function getTitle(): string
    {
        return $this->{$this->getColumnName('title')};
    }

    public function getSlug(): string
    {
        return $this->{$this->getColumnName('slug')};
    }

    public function getContent(): string
    {
        return $this->{$this->getColumnName('content')};
    }


    public function getExcerpt(): ?string
    {
        return $this->{$this->getColumnName('excerpt')};
    }

    public function getViews(): int
    {
        return $this->{$this->getColumnName('views')};
    }

    public function getThumbnail(): ?string
    {
        return $this->{$this->getColumnName('thumbnail')};
    }

    public function getActive(): bool
    {
        return $this->{$this->getColumnName('active')};
    }

    public function getFeatured(): bool
    {
        return $this->{$this->getColumnName('featured')};
    }


    public function getAuthorId(): ?int
    {
        return $this->{$this->getColumnName('author_id')};
    }

    public function getCategoryId(): ?int
    {
        return $this->{$this->getColumnName('category_id')};
    }

    public function getPublishedAt(): ?Carbon {
        return $this->{$this->getColumnName('published_at')};
    }


    public function getCreatedAt(): ?Carbon {
        return $this->{$this->getColumnName('created_at')};
    }


    public function getUpdatedAt(): ?Carbon {
        return $this->{$this->getColumnName('updated_at')};
    }


    /**
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'author_id','id');
    }

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->BelongsTo(Category::class);
    }


    /**
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'views' => 'integer',
            'featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @param User|int|string $author
     *
     * @return $this
     */
    public function attachAuthor(User|int|string $author): News
    {
        $this->author()->associate($author);

        return $this;
    }

    /**
     * @param Category|int|string $category
     *
     * @return $this
     */
    public function attachCategory(Category|int|string $category): News
    {
        $this->category()->associate($category);

        return $this;
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('active', true)
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', Carbon::now());
    }

    /**
     * @param Builder $query
     * @param int     $categoryId
     *
     * @return Builder
     */
    public function scopeOfCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * @param Builder $query
     * @param int     $userId
     *
     * @return Builder
     */
    public function scopeOfUser(Builder $query, int $userId): Builder
    {
        return $query->where('author_id', $userId);
    }

    /**
     * @param Builder $query
     * @param Carbon  $from
     * @param Carbon  $to
     *
     * @return Builder
     */
    public function scopeBetweenDates(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * @param Builder $query
     * @param string  $term
     *
     * @return Builder
     */
    public function scopeSearchByTitle(Builder $query, string $term): Builder
    {
        return $query->where('title', 'like', '%' . $term . '%');
    }

    public function scopeFeatured(Builder $query)
    {
        return $query->where('featured', true);
    }
}
