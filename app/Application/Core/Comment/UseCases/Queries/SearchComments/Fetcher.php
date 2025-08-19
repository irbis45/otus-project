<?php

declare(strict_types=1);

namespace App\Application\Core\Comment\UseCases\Queries\SearchComments;

use App\Application\Core\Comment\DTO\AuthorDTO;
use App\Application\Core\Comment\DTO\CommentDTO;
use App\Application\Core\Comment\DTO\NewsDTO;
use App\Application\Core\Comment\DTO\PaginatedResult;
use App\Application\Core\Comment\DTO\StatusDTO;
use App\Application\Core\Comment\Repositories\CommentRepositoryInterface;
use App\Application\Core\News\Repositories\NewsRepositoryInterface;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Models\Comment;
use DateTimeImmutable;

class Fetcher
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository,
        private NewsRepositoryInterface $newsRepository,
        private UserRepositoryInterface $userRepository
    ) {
    }

        public function fetch(Query $query): PaginatedResult
    {
        $comments = $this->commentRepository->searchPaginated(
            $query->limit,
            $query->offset,
            $query->search,
            $query->newsId,
            $query->status
        );

        $total = $this->commentRepository->searchCount(
            $query->search,
            $query->newsId,
            $query->status
        );

        // Получаем ID авторов и новостей
        $authorIds = array_map(static fn(Comment $comment) => $comment->getAuthorId(), $comments);
        $newsIds = array_map(static fn(Comment $comment) => $comment->getNewsId(), $comments);

        // Загружаем авторов и новости через репозитории
        $authors = $this->userRepository->findByIds($authorIds);
        $news = $this->newsRepository->findByIds($newsIds);

        $commentDTOs = array_map(function (Comment $comment) use ($authors, $news) {
            $author = isset($authors[$comment->getAuthorId()]) ? $authors[$comment->getAuthorId()] : null;
            $newsItem = isset($news[$comment->getNewsId()]) ? $news[$comment->getNewsId()] : null;

            return new CommentDTO(
                id: $comment->getId(),
                text: $comment->getText(),
                author: $author ? new AuthorDTO(
                    id: $author->getId(),
                    name: $author->getName(),
                    email: $author->getEmail(),
                ) : null,
                newsId: $newsItem->getId(),
                status: new StatusDTO(
                    value: $comment->getStatus()->value,
                    label: $comment->getStatus()->label(),
                ),
                parentId: $comment->getParentId(),
                createdAt: $comment->getCreatedAt() ? new DateTimeImmutable(
                    $comment->getCreatedAt()->toDateTimeString()
                ) : null,
                updatedAt: $comment->getUpdatedAt() ? new DateTimeImmutable(
                    $comment->getUpdatedAt()->toDateTimeString()
                ) : null,
                replies: [],
            );
        }, $comments);

        return new PaginatedResult(
            items: $commentDTOs,
            total: $total,
            limit: $query->limit,
            offset: $query->offset
        );
    }
}
