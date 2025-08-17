<?php

namespace App\Application\Core\Comment\UseCases\Queries\FetchAllPagination;

use App\Application\Core\Comment\Repositories\CommentRepositoryInterface;
use App\Application\Core\Comment\DTO\CommentDTO;
use App\Application\Core\Comment\DTO\AuthorDTO;
use App\Application\Core\Comment\DTO\PaginatedResult;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Models\Comment;
use App\Application\Core\Comment\DTO\StatusDTO;

class Fetcher
{
    /**
     * @param CommentRepositoryInterface $commentRepository
     */
    public function __construct(
        private CommentRepositoryInterface $commentRepository,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    /**
     * @param Query $query
     *
     * @return PaginatedResult
     */
    public function fetch(Query $query): PaginatedResult
    {
        $comments = $this->commentRepository->fetchPaginated($query->limit, $query->offset);
        $total    = $this->commentRepository->count();

        $authorIds = array_map(static fn(Comment $comment) => $comment->getAuthorId(), $comments);
        $authors   = $this->userRepository->findByIds($authorIds);

        $commentDTOs = array_map(function (Comment $comment) use ($authors) {
            $statusEnum = $comment->getStatus();

            return new CommentDTO(
                id:                                   $comment->getId(),
                text:                                 $comment->getText(),
                author:                               isset(
                                        $authors[$comment->getAuthorId()]
                                    ) ? new AuthorDTO(
                                        id: $authors[$comment->getAuthorId()]->getId(),
                                        name: $authors[$comment->getAuthorId()]->getName(),
                                        email: $authors[$comment->getAuthorId()]->getEmail(),
                                    ) : null,
                newsId:                               $comment->getNewsId(),
                status:                               new StatusDTO(
                                                          value: $statusEnum->value,
                                                          label: $statusEnum->label(),
                                                      ),
                parentId:                             $comment->getParentId(),
                createdAt:                            $comment->getCreatedAt() ? new \DateTimeImmutable($comment->getCreatedAt()) : null,
                updatedAt:                            $comment->getUpdatedAt() ? new \DateTimeImmutable($comment->getUpdatedAt()) : null,
            );
        }, $comments);

        return new PaginatedResult(
            items: $commentDTOs, total: $total, limit: $query->limit, offset: $query->offset
        );
    }
}
