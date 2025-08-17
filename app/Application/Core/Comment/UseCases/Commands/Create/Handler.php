<?php

namespace App\Application\Core\Comment\UseCases\Commands\Create;

use App\Application\Core\Comment\DTO\AuthorDTO;
use App\Application\Core\Comment\DTO\CommentDTO;
use App\Application\Core\Comment\DTO\StatusDTO;
use App\Application\Core\Comment\Enums\CommentStatus;
use App\Application\Core\Comment\Exceptions\CommentSaveException;
use App\Application\Core\Comment\Repositories\CommentRepositoryInterface;
use App\Models\Comment;

class Handler
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository
    ) {
    }

    public function handle(Command $command): CommentDTO
    {
        $comment = new Comment();

        $comment->{$comment->getColumnName('text')} = $command->text;
        $comment->{$comment->getColumnName('author_id')} = $command->authorId;
        $comment->{$comment->getColumnName('news_id')} = $command->newsId;
        $comment->{$comment->getColumnName('parent_id')} = $command->parentId;
        $comment->{$comment->getColumnName('status')} = CommentStatus::Pending->value;

        $result = $this->commentRepository->save($comment);

        if (!$result) {
            throw new CommentSaveException();
        }

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
    }
}
