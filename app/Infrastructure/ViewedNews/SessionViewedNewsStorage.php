<?php

declare(strict_types=1);

namespace App\Infrastructure\ViewedNews;

use Illuminate\Contracts\Session\Session;
use App\Application\Contracts\ViewedNewsStorageInterface;

class SessionViewedNewsStorage implements ViewedNewsStorageInterface
{
    private const SESSION_KEY = 'viewed_news';

    public function __construct(private Session $session) {}

    public function has(int $newsId): bool
    {
        $viewed = $this->session->get(self::SESSION_KEY, []);
        return in_array($newsId, $viewed, true);
    }

    public function add(int $newsId): void
    {
        $viewed = $this->session->get(self::SESSION_KEY, []);
        if (!in_array($newsId, $viewed, true)) {
            $viewed[] = $newsId;
            $this->session->put(self::SESSION_KEY, $viewed);
        }
    }
}
