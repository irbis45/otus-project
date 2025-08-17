<?php

namespace App\Http\Resources;

use App\Http\Resources\Models\NewsApiModel;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class NewsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        /** @var NewsApiModel $newsDto */
        $newsDto = $this->resource;

        return [
            'id' => $newsDto->id,
            'title' => $newsDto->title,
            'slug' => $newsDto->slug,
            'content' => $newsDto->content,
            'thumbnail' => $newsDto->thumbnail,
            'published_at' => $newsDto->publishedAt,
            'created_at' => $newsDto->createdAt,
            'excerpt' => $newsDto->excerpt,
            'active' => $newsDto->active,
            'featured' => $newsDto->featured,
            'views' => $newsDto->views,
            'updated_at' => $newsDto->updatedAt,
            'author' => $newsDto->author ? [
                'id' => $newsDto->author->id,
                'name' => $newsDto->author->name,
                'email' => $newsDto->author->email,
            ] : null,
            'category' => $newsDto->category ? [
                'id' => $newsDto->category->id,
                'name' => $newsDto->category->name,
                'slug' => $newsDto->category->slug,
            ] : null,
        ];
    }
}
