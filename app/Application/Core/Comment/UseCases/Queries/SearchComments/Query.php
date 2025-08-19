<?php

declare(strict_types=1);

namespace App\Application\Core\Comment\UseCases\Queries\SearchComments;

final readonly class Query
{
    public function __construct(
        public int $limit = 10,
        public int $offset = 0,
        public ?string $search = null,
        public ?int $newsId = null,
        public ?string $status = null,
    ) {
    }

    public static function fromPage(int $page, int $perPage = 10, ?string $search = null, ?int $newsId = null, ?string $status = null): self
    {
        $offset = ($page - 1) * $perPage;
        return new self($perPage, $offset, $search, $newsId, $status);
    }

    public function hasSearch(): bool
    {
        return !empty(trim($this->search ?? ''));
    }

    public function hasFilters(): bool
    {
        return $this->hasSearch() || $this->newsId !== null || $this->status !== null;
    }
}
