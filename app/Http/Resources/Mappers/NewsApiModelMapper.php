<?php

declare(strict_types=1);

namespace App\Http\Resources\Mappers;

use App\Application\Core\News\DTO\NewsDTO;
use App\Http\Resources\Models\AuthorApiModel;
use App\Http\Resources\Models\CategoryApiModel;
use App\Http\Resources\Models\NewsApiModel;

class NewsApiModelMapper
{
    public static function map(NewsDTO $dto): NewsApiModel
    {
        return new NewsApiModel(
            id:          $dto->id,
            title:       $dto->title,
            slug:        $dto->slug,
            content:     $dto->content,
            thumbnail:   $dto->thumbnail,
            publishedAt: $dto->publishedAt?->format('c'),
            createdAt:   $dto->createdAt?->format('c'),
            excerpt:     $dto->excerpt,
            active:      $dto->active,   // ISO 8601
            featured:    $dto->featured,
            views:       $dto->views,
            updatedAt:   $dto->updatedAt?->format('c'),
            author:      $dto->author ? new AuthorApiModel(
                    id: $dto->author->id,
                    name: $dto->author->name,
                    email: $dto->author->email,
                ) : null,
            category:    $dto->category ? new CategoryApiModel(
                    id: $dto->category->id,
                    name: $dto->category->name,
                    slug: $dto->category->slug,
                ) : null,
        );
    }
}
