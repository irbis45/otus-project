<?php

namespace Tests\Unit\Logging;

use App\Logging\TelegramFormatter;
use Monolog\Level;
use Monolog\LogRecord;
use Tests\TestCase;

class TelegramFormatterTest extends TestCase
{
    private TelegramFormatter $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new TelegramFormatter();
    }

    public function test_formatter_implements_formatter_interface()
    {
        $this->assertInstanceOf(\Monolog\Formatter\FormatterInterface::class, $this->formatter);
    }

    public function test_format_single_record()
    {
        $datetime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $level = Level::Error;
        $channel = 'test-channel';
        $message = 'Test error message';

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
            message: $message,
            context: [],
            extra: []
        );

        $result = $this->formatter->format($record);

        $expected = "[2023-01-01 12:00:00] test-channel.Error\n\nTest error message";
        $this->assertEquals($expected, $result);
    }

    public function test_format_with_different_levels()
    {
        $datetime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $channel = 'test-channel';
        $message = 'Test message';

        $levels = [
            Level::Debug,
            Level::Info,
            Level::Warning,
            Level::Error,
            Level::Critical,
            Level::Alert,
            Level::Emergency
        ];

        foreach ($levels as $level) {
            $record = new LogRecord(
                datetime: $datetime,
                channel: $channel,
                level: $level,
                message: $message,
                context: [],
                extra: []
            );

            $result = $this->formatter->format($record);
            $expected = "[2023-01-01 12:00:00] test-channel." . $level->name . "\n\nTest message";
            $this->assertEquals($expected, $result);
        }
    }

    public function test_format_with_different_channels()
    {
        $datetime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $level = Level::Info;
        $message = 'Test message';

        $channels = ['app', 'database', 'queue', 'custom-channel'];

        foreach ($channels as $channel) {
            $record = new LogRecord(
                datetime: $datetime,
                channel: $channel,
                level: $level,
                message: $message,
                context: [],
                extra: []
            );

            $result = $this->formatter->format($record);
            $expected = "[2023-01-01 12:00:00] {$channel}.Info\n\nTest message";
            $this->assertEquals($expected, $result);
        }
    }

    public function test_format_with_special_characters_in_message()
    {
        $datetime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $level = Level::Warning;
        $channel = 'test-channel';
        $message = 'Test message with special chars: @#$%^&*()_+-=[]{}|;:,.<>?';

        $record = new LogRecord(
            datetime: $datetime,
            level: $level,
            channel: $channel,
            message: $message,
            context: [],
            extra: []
        );

        $result = $this->formatter->format($record);
        $expected = "[2023-01-01 12:00:00] test-channel.Warning\n\nTest message with special chars: @#$%^&*()_+-=[]{}|;:,.<>?";
        $this->assertEquals($expected, $result);
    }

    public function test_format_with_multiline_message()
    {
        $datetime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $level = Level::Error;
        $channel = 'test-channel';
        $message = "Line 1\nLine 2\nLine 3";

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: $level,
            message: $message,
            context: [],
            extra: []
        );

        $result = $this->formatter->format($record);
        $expected = "[2023-01-01 12:00:00] test-channel.Error\n\nLine 1\nLine 2\nLine 3";
        $this->assertEquals($expected, $result);
    }

    public function test_format_batch_multiple_records()
    {
        $datetime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $channel = 'test-channel';

        $records = [
            new LogRecord(
                datetime: $datetime,
                channel: $channel,
                level: Level::Info,
                message: 'First message',
                context: [],
                extra: []
            ),
            new LogRecord(
                datetime: $datetime,
                channel: $channel,
                level: Level::Warning,
                message: 'Second message',
                context: [],
                extra: []
            ),
            new LogRecord(
                datetime: $datetime,
                channel: $channel,
                level: Level::Error,
                message: 'Third message',
                context: [],
                extra: []
            )
        ];

        $result = $this->formatter->formatBatch($records);

        $expected = "[2023-01-01 12:00:00] test-channel.Info\n\nFirst message\n\n" .
                   "[2023-01-01 12:00:00] test-channel.Warning\n\nSecond message\n\n" .
                   "[2023-01-01 12:00:00] test-channel.Error\n\nThird message";

        $this->assertEquals($expected, $result);
    }

    public function test_format_batch_empty_array()
    {
        $result = $this->formatter->formatBatch([]);
        $this->assertEquals('', $result);
    }

    public function test_format_batch_single_record()
    {
        $datetime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $channel = 'test-channel';
        $message = 'Single message';

        $record = new LogRecord(
            datetime: $datetime,
            channel: $channel,
            level: Level::Info,
            message: $message,
            context: [],
            extra: []
        );

        $result = $this->formatter->formatBatch([$record]);

        $expected = "[2023-01-01 12:00:00] test-channel.Info\n\nSingle message";
        $this->assertEquals($expected, $result);
    }

    public function test_format_with_empty_message()
    {
        $datetime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $level = Level::Info;
        $channel = 'test-channel';
        $message = '';

        $record = new LogRecord(
            datetime: $datetime,
            level: $level,
            channel: $channel,
            message: $message,
            context: [],
            extra: []
        );

        $result = $this->formatter->format($record);
        $expected = "[2023-01-01 12:00:00] test-channel.Info\n\n";
        $this->assertEquals($expected, $result);
    }

    public function test_format_with_unicode_characters()
    {
        $datetime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $level = Level::Info;
        $channel = 'test-channel';
        $message = 'Тестовое сообщение с кириллицей';

        $record = new LogRecord(
            datetime: $datetime,
            level: $level,
            channel: $channel,
            message: $message,
            context: [],
            extra: []
        );

        $result = $this->formatter->format($record);
        $expected = "[2023-01-01 12:00:00] test-channel.Info\n\nТестовое сообщение с кириллицей";
        $this->assertEquals($expected, $result);
    }
}
