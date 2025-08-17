<?php

declare(strict_types=1);

namespace App\Infrastructure\Eloquent\Repositories\Comments;

use App\Application\Core\Comment\Enums\CommentStatus;
use App\Application\Core\Comment\Repositories\CommentRepositoryInterface;
use App\Models\Comment;

class CommentRepository implements CommentRepositoryInterface
{
    /**
     * @return Comment[]
     */
    public function fetchAllByNewsId(int $newsId): array
    {
        return Comment::query()
                      ->where('news_id', $newsId)
                      ->where('status', CommentStatus::Approved->value)
                      ->orderBy('created_at')
                      ->get()
                      ->all();
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function fetchPaginated(int $limit, int $offset): array
    {
        return Comment::query()
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
        return Comment::query()
                      ->count();
    }

    /**
     * @param int $id
     *
     * @return Comment|null
     */
    public function find(int $id): ?Comment
    {
        return Comment::query()->find($id);
    }


    /**
     * @param Comment $comment
     *
     * @return bool
     */
    public function save(Comment $comment): bool
    {
        return $comment->save();
    }

    /**
     * @param Comment $comment
     *
     * @return bool|null
     */
    public function delete(Comment $comment): ?bool
    {
        return $comment->delete();
    }
}
