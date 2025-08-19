<?php

declare(strict_types=1);

namespace App\Application\Core\User\UseCases\Queries\SearchUsers;

final readonly class Query
{
    public function __construct(
        public int $limit = 10,
        public int $offset = 0,
        public ?string $search = null,
    ) {
    }

    public static function fromPage(int $page, int $perPage = 10, ?string $search = null): self
    {
        $offset = ($page - 1) * $perPage;
        return new self($perPage, $offset, $search);
    }

    public function hasSearch(): bool
    {
        return !empty(trim($this->search ?? ''));
    }
}
