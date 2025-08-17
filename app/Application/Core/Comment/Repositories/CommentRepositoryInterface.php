<?php

declare(strict_types=1);

namespace App\Application\Core\Comment\Repositories;

use App\Models\Comment;

interface CommentRepositoryInterface
{
    public function fetchAllByNewsId(int $newsId): array;

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function fetchPaginated(int $limit, int $offset): array;

    /**
     * @return int
     */
    public function count(): int;

    /**
     * @param int $id
     *
     * @return Comment|null
     */
    public function find(int $id): ?Comment;

    /**
     * @param Comment $comment
     *
     * @return bool
     */
    public function save(Comment $comment): bool;

    /**
     * @param Comment $comment
     *
     * @return bool|null
     */
    public function delete(Comment $comment): ?bool;
}
