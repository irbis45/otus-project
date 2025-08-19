<?php

declare(strict_types=1);

namespace App\Helpers;

class SearchHelper
{
    /**
     * Подсвечивает найденный текст в строке
     */
    public static function highlight(string $text, ?string $search): string
    {
        if (empty($search)) {
            return $text;
        }

        $search = preg_quote($search, '/');
        return preg_replace("/($search)/i", '<span class="search-highlight">$1</span>', $text);
    }

    /**
     * Обрезает текст с подсветкой поиска
     */
    public static function highlightExcerpt(string $text, ?string $search, int $length = 100): string
    {
        if (empty($search)) {
            return mb_strlen($text) > $length ? mb_substr($text, 0, $length) . '...' : $text;
        }

        $searchLower = mb_strtolower($search);
        $textLower = mb_strtolower($text);
        $pos = mb_strpos($textLower, $searchLower);

        if ($pos === false) {
            return mb_strlen($text) > $length ? mb_substr($text, 0, $length) . '...' : $text;
        }

        $start = max(0, (int)($pos - $length / 2));
        $excerpt = mb_substr($text, $start, $length);
        
        if ($start > 0) {
            $excerpt = '...' . $excerpt;
        }
        
        if ($start + $length < mb_strlen($text)) {
            $excerpt .= '...';
        }

        return self::highlight($excerpt, $search);
    }
}
