<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\News;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_category_extends_base_model()
    {
        $category = new Category();
        $this->assertInstanceOf(\App\Models\BaseModel::class, $category);
    }

    public function test_category_has_fillable_attributes()
    {
        $category = new Category();
        $expectedFillable = ['name', 'description', 'active'];
        $this->assertEquals($expectedFillable, $category->getFillable());
    }

    public function test_get_column_name_returns_mapped_value()
    {
        $category = new Category();
        $category->id = 1;
        $category->name = 'Test Category';
        
        $this->assertEquals('id', $category->getColumnName('id'));
        $this->assertEquals('name', $category->getColumnName('name'));
        $this->assertEquals('unknown', $category->getColumnName('unknown'));
    }

    public function test_get_id_returns_category_id()
    {
        $category = new Category();
        $category->id = 123;
        $this->assertEquals(123, $category->getId());
    }

    public function test_get_name_returns_category_name()
    {
        $category = new Category();
        $category->name = 'Test Category Name';
        $this->assertEquals('Test Category Name', $category->getName());
    }

    public function test_get_description_returns_category_description()
    {
        $category = new Category();
        $category->description = 'Test category description';
        $this->assertEquals('Test category description', $category->getDescription());
    }

    public function test_get_description_returns_null_when_not_set()
    {
        $category = new Category();
        $this->assertNull($category->getDescription());
    }

    public function test_get_slug_returns_category_slug()
    {
        $category = new Category();
        $category->slug = 'test-category-slug';
        $this->assertEquals('test-category-slug', $category->getSlug());
    }

    public function test_get_active_returns_category_active_status()
    {
        $category = new Category();
        $category->active = true;
        $this->assertTrue($category->getActive());
    }

    public function test_slug_from_returns_name_field()
    {
        $this->assertEquals('name', Category::slugFrom());
    }

    public function test_category_has_news_relationship()
    {
        $category = new Category();
        $this->assertTrue(method_exists($category, 'news'));
    }

    public function test_category_has_published_news_relationship()
    {
        $category = new Category();
        $this->assertTrue(method_exists($category, 'publishedNews'));
    }

    public function test_scope_published_filters_active_and_published_news()
    {
        $category = new Category();
        $query = Mockery::mock(Builder::class);
        
        $query->shouldReceive('where')
            ->with('active', true)
            ->once()
            ->andReturnSelf();
        
        $query->shouldReceive('whereNotNull')
            ->with('published_at')
            ->once()
            ->andReturnSelf();
        
        $query->shouldReceive('where')
            ->with('published_at', '<=', Mockery::type(Carbon::class))
            ->once()
            ->andReturnSelf();
        
        $result = $category->scopePublished($query);
        $this->assertSame($query, $result);
    }

    public function test_scope_active_filters_active_categories()
    {
        $category = new Category();
        $query = Mockery::mock(Builder::class);
        
        $query->shouldReceive('where')
            ->with('active', true)
            ->once()
            ->andReturnSelf();
        
        $result = $category->scopeActive($query);
        $this->assertSame($query, $result);
    }

    public function test_category_uses_has_slug_trait()
    {
        $category = new Category();
        $this->assertTrue(method_exists($category, 'makeSlug'));
    }

    public function test_category_uses_has_factory_trait()
    {
        $category = new Category();
        $this->assertTrue(method_exists($category, 'factory'));
    }

    public function test_category_timestamps_disabled()
    {
        $category = new Category();
        $this->assertFalse($category->timestamps);
    }

    public function test_category_casts_active_to_boolean()
    {
        $category = new Category();
        $casts = $category->getCasts();
        $this->assertEquals('boolean', $casts['active']);
    }
}
