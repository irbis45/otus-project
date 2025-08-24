<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\News;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;

class NewsTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_news_extends_base_model()
    {
        $news = new News();
        $this->assertInstanceOf(\App\Models\BaseModel::class, $news);
    }

    public function test_news_has_fillable_attributes()
    {
        $news = new News();
        $expectedFillable = [
            'title', 'content', 'thumbnail', 'active', 'excerpt',
            'featured', 'published_at', 'views', 'author_id', 'category_id'
        ];
        $this->assertEquals($expectedFillable, $news->getFillable());
    }

    public function test_get_title_returns_news_title()
    {
        $news = new News();
        $news->title = 'Test News Title';
        $this->assertEquals('Test News Title', $news->getTitle());
    }

    public function test_get_slug_returns_news_slug()
    {
        $news = new News();
        $news->slug = 'test-news-slug';
        $this->assertEquals('test-news-slug', $news->getSlug());
    }

    public function test_get_content_returns_news_content()
    {
        $news = new News();
        $news->content = 'Test news content';
        $this->assertEquals('Test news content', $news->getContent());
    }

    public function test_get_excerpt_returns_news_excerpt()
    {
        $news = new News();
        $news->excerpt = 'Test excerpt';
        $this->assertEquals('Test excerpt', $news->getExcerpt());
    }

    public function test_get_views_returns_news_views()
    {
        $news = new News();
        $news->views = 42;
        $this->assertEquals(42, $news->getViews());
    }

    public function test_get_active_returns_news_active_status()
    {
        $news = new News();
        $news->active = true;
        $this->assertTrue($news->getActive());
    }

    public function test_get_featured_returns_news_featured_status()
    {
        $news = new News();
        $news->featured = true;
        $this->assertTrue($news->getFeatured());
    }

    public function test_get_author_id_returns_news_author_id()
    {
        $news = new News();
        $news->author_id = 456;
        $this->assertEquals(456, $news->getAuthorId());
    }

    public function test_get_category_id_returns_news_category_id()
    {
        $news = new News();
        $news->category_id = 789;
        $this->assertEquals(789, $news->getCategoryId());
    }

    public function test_get_published_at_returns_carbon_instance()
    {
        $news = new News();
        $news->published_at = '2023-01-01 12:00:00';
        $result = $news->getPublishedAt();
        $this->assertInstanceOf(Carbon::class, $result);
    }

    public function test_news_has_author_relationship()
    {
        $news = new News();
        $this->assertTrue(method_exists($news, 'author'));
    }

    public function test_news_has_category_relationship()
    {
        $news = new News();
        $this->assertTrue(method_exists($news, 'category'));
    }

    public function test_news_has_comments_relationship()
    {
        $news = new News();
        $this->assertTrue(method_exists($news, 'comments'));
    }

    public function test_attach_author_associates_author()
    {
        $news = new News();
        $user = new User();
        $user->id = 1;
        $result = $news->attachAuthor($user);
        $this->assertSame($news, $result);
        $this->assertEquals(1, $news->author_id);
    }

    public function test_attach_category_associates_category()
    {
        $news = new News();
        $category = new Category();
        $category->id = 1;
        $result = $news->attachCategory($category);
        $this->assertSame($news, $result);
        $this->assertEquals(1, $news->category_id);
    }

    public function test_scope_published_filters_active_and_published_news()
    {
        $news = new News();
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

        $result = $news->scopePublished($query);
        $this->assertSame($query, $result);
    }


    public function test_scope_featured_filters_featured_news()
    {
        $news = new News();
        $query = Mockery::mock(Builder::class);

        $query->shouldReceive('where')
            ->with('featured', true)
            ->once()
            ->andReturnSelf();

        $result = $news->scopeFeatured($query);
        $this->assertSame($query, $result);
    }

    public function test_news_uses_has_slug_trait()
    {
        $news = new News();
        $this->assertTrue(method_exists($news, 'makeSlug'));
    }
}
