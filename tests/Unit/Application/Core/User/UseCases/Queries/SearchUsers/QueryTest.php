<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Core\User\UseCases\Queries\SearchUsers;

use App\Application\Core\User\UseCases\Queries\SearchUsers\Query;
use Tests\TestCase;

class QueryTest extends TestCase
{
    public function test_constructor_with_all_parameters(): void
    {
        $query = new Query(20, 40, 'test search');

        $this->assertEquals(20, $query->limit);
        $this->assertEquals(40, $query->offset);
        $this->assertEquals('test search', $query->search);
    }

    public function test_constructor_with_default_parameters(): void
    {
        $query = new Query();

        $this->assertEquals(10, $query->limit);
        $this->assertEquals(0, $query->offset);
        $this->assertNull($query->search);
    }

    public function test_from_page_with_search(): void
    {
        $query = Query::fromPage(3, 15, 'john');

        $this->assertEquals(15, $query->limit);
        $this->assertEquals(30, $query->offset); // (3-1) * 15
        $this->assertEquals('john', $query->search);
    }

    public function test_from_page_without_search(): void
    {
        $query = Query::fromPage(2, 25);

        $this->assertEquals(25, $query->limit);
        $this->assertEquals(25, $query->offset); // (2-1) * 25
        $this->assertNull($query->search);
    }

    public function test_from_page_with_page_one(): void
    {
        $query = Query::fromPage(1, 10, 'test');

        $this->assertEquals(10, $query->limit);
        $this->assertEquals(0, $query->offset); // (1-1) * 10
        $this->assertEquals('test', $query->search);
    }

    public function test_has_search_with_search_query(): void
    {
        $query = new Query(10, 0, 'test search');
        $this->assertTrue($query->hasSearch());
    }

    public function test_has_search_without_search_query(): void
    {
        $query = new Query(10, 0);
        $this->assertFalse($query->hasSearch());
    }

    public function test_has_search_with_empty_string(): void
    {
        $query = new Query(10, 0, '');
        $this->assertFalse($query->hasSearch());
    }

    public function test_has_search_with_whitespace_only(): void
    {
        $query = new Query(10, 0, '   ');
        $this->assertFalse($query->hasSearch());
    }

    public function test_immutability(): void
    {
        $query = new Query(10, 0, 'test');
        
        // Проверяем, что свойства readonly (нельзя изменить)
        $this->expectException(\Error::class);
        $query->limit = 20;
    }
}
