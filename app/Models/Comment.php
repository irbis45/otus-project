<?php

namespace App\Models;

use App\Application\Core\Comment\Enums\CommentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use App\Models\News;
use App\Models\User;

class Comment extends BaseModel
{
    use HasFactory;

    /** @var string[]  */
    protected $fillable = [
        'author_id',
        'parent_id',
        'news_id',
        'text',
        'status'
    ];


    protected $columnMap = [
        'id' => 'id',
        'parent_id' => 'parent_id',
        'author_id' => 'author_id',
        'news_id' => 'news_id',
        'text' => 'text',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        'status' => 'status',
    ];

    protected $casts = [
        'status' => CommentStatus::class,
    ];

    /**
     * @var Comment[]|null
     */
    protected ?array $replies = null;

    public function getColumnName($property)
    {
        return $this->columnMap[$property] ?? $property;
    }


    public function getId(): int
    {
        return $this->{$this->getColumnName('id')};
    }

    public function getText(): string
    {
        return $this->{$this->getColumnName('text')};
    }

    public function getParentId(): ?int
    {
        return $this->{$this->getColumnName('parent_id')};
    }

    public function getStatus(): CommentStatus
    {
        return $this->{$this->getColumnName('status')};
    }

    public function getAuthorId(): ?int
    {
        return $this->{$this->getColumnName('author_id')};
    }

    public function getNewsId(): int
    {
        return $this->{$this->getColumnName('news_id')};
    }

/*    public function getNewsItem(): ?News
    {
        return $this->newsItem;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }*/

    public function getCreatedAt(): ?Carbon {
        return $this->{$this->getColumnName('created_at')};
    }


    public function getUpdatedAt(): ?Carbon {
        return $this->{$this->getColumnName('updated_at')};
    }

    /**
     * @return belongsTo
     */
    public function parent(): belongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Один дочерний уровень
     *
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Получение нескольких дочерних уровней
     *
     * @return HasMany
     */
    public function childrenComments(): HasMany
    {
        return $this->hasMany(Comment::class)->with('comments');
    }

    /**
     * Получить новость, которой принадлежит комментарий
     *
     * @return BelongsTo
     */
    public function newsItem(): BelongsTo
    {
        return $this->belongsTo(News::class, 'news_id');
    }

    /**
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    /**
     * @param Comment[] $replies
     */
    public function setReplies(array $replies): void
    {
        $this->replies = $replies;
    }

    /**
     * @return Comment[]|null
     */
    public function getReplies(): ?array
    {
        return $this->replies;
    }
}
