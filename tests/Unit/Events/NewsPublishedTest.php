<?php

namespace Tests\Unit\Events;

use App\Events\NewsPublished;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsPublishedTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_uses_correct_traits()
    {
        $event = new NewsPublished(1, 'Test Title', 'Test Content');
        
        $traits = class_uses($event);
        
        $this->assertContains('Illuminate\Foundation\Events\Dispatchable', $traits);
        $this->assertContains('Illuminate\Broadcasting\InteractsWithSockets', $traits);
        $this->assertContains('Illuminate\Queue\SerializesModels', $traits);
    }

    public function test_event_constructor_sets_properties_correctly()
    {
        $id = 123;
        $title = 'Breaking News Title';
        $content = 'This is the content of the breaking news article.';

        $event = new NewsPublished($id, $title, $content);

        $this->assertEquals($id, $event->id);
        $this->assertEquals($title, $event->title);
        $this->assertEquals($content, $event->content);
    }

    public function test_event_properties_are_readonly()
    {
        $event = new NewsPublished(1, 'Test Title', 'Test Content');

        $reflection = new \ReflectionClass($event);
        
        $idProperty = $reflection->getProperty('id');
        $titleProperty = $reflection->getProperty('title');
        $contentProperty = $reflection->getProperty('content');

        $this->assertTrue($idProperty->isReadOnly());
        $this->assertTrue($titleProperty->isReadOnly());
        $this->assertTrue($contentProperty->isReadOnly());
    }

    public function test_event_properties_are_public()
    {
        $event = new NewsPublished(1, 'Test Title', 'Test Content');

        $reflection = new \ReflectionClass($event);
        
        $idProperty = $reflection->getProperty('id');
        $titleProperty = $reflection->getProperty('title');
        $contentProperty = $reflection->getProperty('content');

        $this->assertTrue($idProperty->isPublic());
        $this->assertTrue($titleProperty->isPublic());
        $this->assertTrue($contentProperty->isPublic());
    }

    public function test_broadcast_on_returns_private_channel()
    {
        $event = new NewsPublished(1, 'Test Title', 'Test Content');
        
        $channels = $event->broadcastOn();
        
        $this->assertIsArray($channels);
        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertEquals('private-channel-name', $channels[0]->name);
    }

    public function test_event_with_different_data_types()
    {
        $id = 999;
        $title = 'Another News Title';
        $content = 'Different content here';

        $event = new NewsPublished($id, $title, $content);

        $this->assertIsInt($event->id);
        $this->assertIsString($event->title);
        $this->assertIsString($event->content);
        
        $this->assertEquals($id, $event->id);
        $this->assertEquals($title, $event->title);
        $this->assertEquals($content, $event->content);
    }

    public function test_event_with_special_characters()
    {
        $id = 456;
        $title = 'News with special chars: @#$%^&*()_+-=[]{}|;:,.<>?';
        $content = 'Content with special chars: @#$%^&*()_+-=[]{}|;:,.<>?';

        $event = new NewsPublished($id, $title, $content);

        $this->assertEquals($id, $event->id);
        $this->assertEquals($title, $event->title);
        $this->assertEquals($content, $event->content);
    }

    public function test_event_with_unicode_characters()
    {
        $id = 789;
        $title = 'Новость с кириллицей';
        $content = 'Содержание новости с кириллицей';

        $event = new NewsPublished($id, $title, $content);

        $this->assertEquals($id, $event->id);
        $this->assertEquals($title, $event->title);
        $this->assertEquals($content, $event->content);
    }

    public function test_event_with_long_content()
    {
        $id = 111;
        $title = 'Short Title';
        $content = str_repeat('Very long content. ', 100);

        $event = new NewsPublished($id, $title, $content);

        $this->assertEquals($id, $event->id);
        $this->assertEquals($title, $event->title);
        $this->assertEquals($content, $event->content);
        $this->assertGreaterThan(1000, strlen($event->content));
    }

    public function test_event_with_empty_strings()
    {
        $id = 222;
        $title = '';
        $content = '';

        $event = new NewsPublished($id, $title, $content);

        $this->assertEquals($id, $event->id);
        $this->assertEquals($title, $event->title);
        $this->assertEquals($content, $event->content);
        $this->assertEmpty($event->title);
        $this->assertEmpty($event->content);
    }

    public function test_event_with_zero_id()
    {
        $id = 0;
        $title = 'Title with zero ID';
        $content = 'Content for zero ID';

        $event = new NewsPublished($id, $title, $content);

        $this->assertEquals($id, $event->id);
        $this->assertEquals($title, $event->title);
        $this->assertEquals($content, $event->content);
        $this->assertSame(0, $event->id);
    }

    public function test_event_with_negative_id()
    {
        $id = -1;
        $title = 'Title with negative ID';
        $content = 'Content for negative ID';

        $event = new NewsPublished($id, $title, $content);

        $this->assertEquals($id, $event->id);
        $this->assertEquals($title, $event->title);
        $this->assertEquals($content, $event->content);
        $this->assertLessThan(0, $event->id);
    }

    public function test_event_with_html_content()
    {
        $id = 333;
        $title = 'Title with <strong>HTML</strong>';
        $content = '<p>Content with <em>HTML</em> tags</p>';

        $event = new NewsPublished($id, $title, $content);

        $this->assertEquals($id, $event->id);
        $this->assertEquals($title, $event->title);
        $this->assertEquals($content, $event->content);
        $this->assertStringContainsString('<strong>', $event->title);
        $this->assertStringContainsString('<em>', $event->content);
    }

    public function test_event_with_multiline_content()
    {
        $id = 444;
        $title = 'Multiline Title';
        $content = "Line 1\nLine 2\nLine 3";

        $event = new NewsPublished($id, $title, $content);

        $this->assertEquals($id, $event->id);
        $this->assertEquals($title, $event->title);
        $this->assertEquals($content, $event->content);
        $this->assertStringContainsString("\n", $event->content);
        $this->assertEquals(3, substr_count($event->content, "\n") + 1);
    }

    public function test_event_serialization()
    {
        $id = 555;
        $title = 'Serializable Title';
        $content = 'Serializable Content';

        $event = new NewsPublished($id, $title, $content);

        // Проверяем, что событие можно сериализовать
        $serialized = serialize($event);
        $this->assertIsString($serialized);

        // Проверяем, что событие можно десериализовать
        $unserialized = unserialize($serialized);
        $this->assertInstanceOf(NewsPublished::class, $unserialized);
        $this->assertEquals($id, $unserialized->id);
        $this->assertEquals($title, $unserialized->title);
        $this->assertEquals($content, $unserialized->content);
    }

    public function test_event_implements_broadcasting_contract()
    {
        $event = new NewsPublished(1, 'Test Title', 'Test Content');
        
        // Проверяем, что событие не реализует ShouldBroadcast
        $this->assertNotInstanceOf(\Illuminate\Contracts\Broadcasting\ShouldBroadcast::class, $event);
        
        // Но имеет метод broadcastOn
        $this->assertTrue(method_exists($event, 'broadcastOn'));
        $this->assertIsArray($event->broadcastOn());
    }

    public function test_event_can_be_dispatched()
    {
        $event = new NewsPublished(1, 'Test Title', 'Test Content');
        
        // Проверяем, что событие можно диспатчить
        $this->assertTrue(method_exists($event, 'dispatch'));
        
        // Проверяем, что событие использует трейт Dispatchable
        $traits = class_uses($event);
        $this->assertContains('Illuminate\Foundation\Events\Dispatchable', $traits);
    }

    public function test_event_broadcast_channels_are_immutable()
    {
        $event = new NewsPublished(1, 'Test Title', 'Test Content');
        
        $channels = $event->broadcastOn();
        $originalCount = count($channels);
        
        // Попытка изменить массив не должна влиять на оригинал
        $channels[] = new \Illuminate\Broadcasting\Channel('test');
        
        $this->assertCount($originalCount, $event->broadcastOn());
        $this->assertCount($originalCount + 1, $channels);
    }
}
