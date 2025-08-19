<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use App\Helpers\SearchHelper;
use Tests\TestCase;

class SearchHelperTest extends TestCase
{
    public function test_highlight_with_search_query(): void
    {
        $text = 'John Doe is a developer';
        $search = 'John';
        
        $result = SearchHelper::highlight($text, $search);
        
        $this->assertEquals(
            '<span class="search-highlight">John</span> Doe is a developer',
            $result
        );
    }

    public function test_highlight_with_case_insensitive_search(): void
    {
        $text = 'John Doe is a developer';
        $search = 'john';
        
        $result = SearchHelper::highlight($text, $search);
        
        $this->assertEquals(
            '<span class="search-highlight">John</span> Doe is a developer',
            $result
        );
    }

    public function test_highlight_with_multiple_occurrences(): void
    {
        $text = 'John Doe and John Smith are developers';
        $search = 'John';
        
        $result = SearchHelper::highlight($text, $search);
        
        $this->assertEquals(
            '<span class="search-highlight">John</span> Doe and <span class="search-highlight">John</span> Smith are developers',
            $result
        );
    }

    public function test_highlight_without_search_query(): void
    {
        $text = 'John Doe is a developer';
        
        $result = SearchHelper::highlight($text, null);
        
        $this->assertEquals($text, $result);
    }

    public function test_highlight_with_empty_search_query(): void
    {
        $text = 'John Doe is a developer';
        
        $result = SearchHelper::highlight($text, '');
        
        $this->assertEquals($text, $result);
    }

    public function test_highlight_with_special_characters(): void
    {
        $text = 'User email: john.doe@example.com';
        $search = 'john.doe@example.com';
        
        $result = SearchHelper::highlight($text, $search);
        
        $this->assertEquals(
            'User email: <span class="search-highlight">john.doe@example.com</span>',
            $result
        );
    }

    public function test_highlight_excerpt_with_search_query(): void
    {
        $text = 'This is a very long text about John Doe who is a developer working on Laravel projects';
        $search = 'John';
        $length = 30;
        
        $result = SearchHelper::highlightExcerpt($text, $search, $length);
        
        $this->assertStringContainsString('<span class="search-highlight">John</span>', $result);
        $this->assertLessThanOrEqual($length + 50, mb_strlen(strip_tags($result))); // +50 для HTML тегов
    }

    public function test_highlight_excerpt_without_search_query(): void
    {
        $text = 'This is a very long text about John Doe who is a developer working on Laravel projects';
        $length = 30;
        
        $result = SearchHelper::highlightExcerpt($text, null, $length);
        
        $this->assertStringContainsString('This is a very long text', $result);
        $this->assertStringEndsWith('...', $result);
    }

    public function test_highlight_excerpt_with_short_text(): void
    {
        $text = 'Short text';
        $search = 'text';
        $length = 50;
        
        $result = SearchHelper::highlightExcerpt($text, $search, $length);
        
        $this->assertEquals('Short <span class="search-highlight">text</span>', $result);
    }

    public function test_highlight_excerpt_with_search_at_beginning(): void
    {
        $text = 'John Doe is a developer working on Laravel projects';
        $search = 'John';
        $length = 20;
        
        $result = SearchHelper::highlightExcerpt($text, $search, $length);
        
        $this->assertStringStartsWith('<span class="search-highlight">John</span>', $result);
        $this->assertStringEndsWith('...', $result);
    }

    public function test_highlight_excerpt_with_search_at_end(): void
    {
        $text = 'This is a very long text about John Doe';
        $search = 'Doe';
        $length = 20;
        
        $result = SearchHelper::highlightExcerpt($text, $search, $length);
        
        $this->assertStringStartsWith('...', $result);
        $this->assertStringContainsString('<span class="search-highlight">Doe</span>', $result);
    }

    public function test_highlight_excerpt_with_search_in_middle(): void
    {
        $text = 'This is a very long text about John Doe who is a developer';
        $search = 'John';
        $length = 25;
        
        $result = SearchHelper::highlightExcerpt($text, $search, $length);
        
        $this->assertStringStartsWith('...', $result);
        $this->assertStringContainsString('<span class="search-highlight">John</span>', $result);
        $this->assertStringEndsWith('...', $result);
    }
}
