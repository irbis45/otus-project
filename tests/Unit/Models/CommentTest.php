<?php

namespace Tests\Unit\Models;

use App\Models\Comment;
use App\Models\News;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CommentTest extends TestCase
{
    public function test_comment_extends_base_model()
    {
        $comment = new Comment();
        $this->assertInstanceOf(\App\Models\BaseModel::class, $comment);
    }

    public function test_comment_has_fillable_attributes()
    {
        $comment = new Comment();
        $expectedFillable = ['author_id', 'parent_id', 'news_id', 'text', 'status'];
        $this->assertEquals($expectedFillable, $comment->getFillable());
    }

    public function test_get_column_name_returns_mapped_value()
    {
        $comment = new Comment();
        $comment->id = 1;
        $comment->text = 'Test comment';
        
        $this->assertEquals('id', $comment->getColumnName('id'));
        $this->assertEquals('text', $comment->getColumnName('text'));
        $this->assertEquals('unknown', $comment->getColumnName('unknown'));
    }

    public function test_get_id_returns_comment_id()
    {
        $comment = new Comment();
        $comment->id = 123;
        $this->assertEquals(123, $comment->getId());
    }

    public function test_get_text_returns_comment_text()
    {
        $comment = new Comment();
        $comment->text = 'Test comment text';
        $this->assertEquals('Test comment text', $comment->getText());
    }

    public function test_get_parent_id_returns_comment_parent_id()
    {
        $comment = new Comment();
        $comment->parent_id = 456;
        $this->assertEquals(456, $comment->getParentId());
    }

    public function test_get_parent_id_returns_null_when_not_set()
    {
        $comment = new Comment();
        $this->assertNull($comment->getParentId());
    }

    public function test_get_status_returns_comment_status()
    {
        $comment = new Comment();
        $comment->status = 'approved';
        $result = $comment->getStatus();
        $this->assertInstanceOf(\App\Application\Core\Comment\Enums\CommentStatus::class, $result);
        $this->assertEquals('approved', $result->value);
    }

    public function test_get_author_id_returns_comment_author_id()
    {
        $comment = new Comment();
        $comment->author_id = 789;
        $this->assertEquals(789, $comment->getAuthorId());
    }

    public function test_get_author_id_returns_null_when_not_set()
    {
        $comment = new Comment();
        $this->assertNull($comment->getAuthorId());
    }

    public function test_get_news_id_returns_comment_news_id()
    {
        $comment = new Comment();
        $comment->news_id = 101;
        $this->assertEquals(101, $comment->getNewsId());
    }

    public function test_get_created_at_returns_carbon_instance()
    {
        $comment = new Comment();
        $comment->created_at = '2023-01-01 12:00:00';
        $result = $comment->getCreatedAt();
        $this->assertInstanceOf(Carbon::class, $result);
    }

    public function test_get_updated_at_returns_carbon_instance()
    {
        $comment = new Comment();
        $comment->updated_at = '2023-01-01 12:00:00';
        $result = $comment->getUpdatedAt();
        $this->assertInstanceOf(Carbon::class, $result);
    }

    public function test_comment_has_parent_relationship()
    {
        $comment = new Comment();
        $this->assertTrue(method_exists($comment, 'parent'));
    }

    public function test_comment_has_comments_relationship()
    {
        $comment = new Comment();
        $this->assertTrue(method_exists($comment, 'comments'));
    }

    public function test_comment_has_children_comments_relationship()
    {
        $comment = new Comment();
        $this->assertTrue(method_exists($comment, 'childrenComments'));
    }

    public function test_comment_has_news_item_relationship()
    {
        $comment = new Comment();
        $this->assertTrue(method_exists($comment, 'newsItem'));
    }

    public function test_comment_has_author_relationship()
    {
        $comment = new Comment();
        $this->assertTrue(method_exists($comment, 'author'));
    }

    public function test_set_replies_sets_replies_array()
    {
        $comment = new Comment();
        $replies = [new Comment(), new Comment()];
        
        $comment->setReplies($replies);
        $this->assertEquals($replies, $comment->getReplies());
    }

    public function test_get_replies_returns_replies_array()
    {
        $comment = new Comment();
        $replies = [new Comment(), new Comment()];
        $comment->setReplies($replies);
        
        $result = $comment->getReplies();
        $this->assertEquals($replies, $result);
    }

    public function test_get_replies_returns_null_when_not_set()
    {
        $comment = new Comment();
        $this->assertNull($comment->getReplies());
    }

    public function test_comment_uses_has_factory_trait()
    {
        $comment = new Comment();
        $this->assertTrue(method_exists($comment, 'factory'));
    }

    public function test_comment_casts_status_to_comment_status_enum()
    {
        $comment = new Comment();
        $casts = $comment->getCasts();
        $this->assertEquals('App\Application\Core\Comment\Enums\CommentStatus', $casts['status']);
    }
}
