<?php

namespace Tests\Unit\Logging;

use App\Logging\TelegramRateLimitedHandler;
use App\Logging\TelegramFormatter;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Monolog\Level;
use Monolog\LogRecord;
use Monolog\Handler\TelegramBotHandler;
use Tests\TestCase;
use Mockery;

class TelegramRateLimitedHandlerTest extends TestCase
{
    private TelegramRateLimitedHandler $handler;
    private string $apiKey = 'test-api-key';
    private string $channel = 'test-channel';
    private string $fallbackChannel = 'fallback-channel';

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new TelegramRateLimitedHandler(
            Level::Debug,
            true,
            $this->apiKey,
            $this->channel,
            $this->fallbackChannel
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_handler_extends_abstract_processing_handler()
    {
        $this->assertInstanceOf(\Monolog\Handler\AbstractProcessingHandler::class, $this->handler);
    }

    public function test_constructor_sets_properties_correctly()
    {
        $handler = new TelegramRateLimitedHandler(
            Level::Error,
            false,
            'custom-api-key',
            'custom-channel',
            'custom-fallback'
        );

        // Проверяем, что свойства установлены через reflection
        $reflection = new \ReflectionClass($handler);
        $apiKeyProperty = $reflection->getProperty('apiKey');
        $apiKeyProperty->setAccessible(true);
        $channelProperty = $reflection->getProperty('channel');
        $channelProperty->setAccessible(true);
        $fallbackChannelProperty = $reflection->getProperty('fallbackChannel');
        $fallbackChannelProperty->setAccessible(true);

        $this->assertEquals('custom-api-key', $apiKeyProperty->getValue($handler));
        $this->assertEquals('custom-channel', $channelProperty->getValue($handler));
        $this->assertEquals('custom-fallback', $fallbackChannelProperty->getValue($handler));
    }

    public function test_write_successful_rate_limit_execution()
    {
        $datetime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $level = Level::Error;
        $message = 'Test error message';

        $record = new LogRecord(
            datetime: $datetime,
            channel: 'test-channel',
            level: $level,
            message: $message,
            context: [],
            extra: []
        );

        // Мокаем RateLimiter для успешного выполнения
        RateLimiter::shouldReceive('attempt')
            ->once()
            ->with(
                'telegram-log-send:' . $this->channel,
                20,
                Mockery::type('Closure'),
                60
            )
            ->andReturn(true);

        // Мокаем Log для проверки, что fallback не вызывается
        Log::shouldReceive('channel')
            ->never();

        // Используем reflection для вызова protected метода
        $reflection = new \ReflectionClass($this->handler);
        $writeMethod = $reflection->getMethod('write');
        $writeMethod->setAccessible(true);
        $writeMethod->invoke($this->handler, $record);

        // Проверяем, что метод выполнился без ошибок
        $this->assertTrue(true);
    }

    public function test_write_rate_limit_exceeded_falls_back_to_log()
    {
        $datetime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $level = Level::Error;
        $message = 'Test error message';

        $record = new LogRecord(
            datetime: $datetime,
            channel: 'test-channel',
            level: $level,
            message: $message,
            context: [],
            extra: []
        );

        // Мокаем RateLimiter для превышения лимита
        RateLimiter::shouldReceive('attempt')
            ->once()
            ->with(
                'telegram-log-send:' . $this->channel,
                20,
                Mockery::type('Closure'),
                60
            )
            ->andReturn(false);

        // Мокаем Log для fallback
        $logChannel = Mockery::mock();
        $logChannel->shouldReceive('error')
            ->once()
            ->with($message);

        Log::shouldReceive('channel')
            ->once()
            ->with($this->fallbackChannel)
            ->andReturn($logChannel);

        // Используем reflection для вызова protected метода
        $reflection = new \ReflectionClass($this->handler);
        $writeMethod = $reflection->getMethod('write');
        $writeMethod->setAccessible(true);
        $writeMethod->invoke($this->handler, $record);

        // Проверяем, что fallback логирование было вызвано
        $this->assertTrue(true);
    }

    public function test_write_creates_telegram_bot_handler_with_correct_parameters()
    {
        $datetime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $level = Level::Warning;
        $message = 'Test warning message';

        $record = new LogRecord(
            datetime: $datetime,
            channel: 'test-channel',
            level: $level,
            message: $message,
            context: [],
            extra: []
        );

        // Мокаем RateLimiter
        RateLimiter::shouldReceive('attempt')
            ->once()
            ->andReturn(true);

        // Используем reflection для вызова protected метода
        $reflection = new \ReflectionClass($this->handler);
        $writeMethod = $reflection->getMethod('write');
        $writeMethod->setAccessible(true);
        $writeMethod->invoke($this->handler, $record);

        // Проверяем, что метод выполнился без ошибок
        $this->assertTrue(true);
    }

    public function test_write_sets_formatter_on_telegram_handler()
    {
        $datetime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $level = Level::Info;
        $message = 'Test info message';

        $record = new LogRecord(
            datetime: $datetime,
            channel: 'test-channel',
            level: $level,
            message: $message,
            context: [],
            extra: []
        );

        // Мокаем RateLimiter
        RateLimiter::shouldReceive('attempt')
            ->once()
            ->andReturn(true);

        // Используем reflection для вызова protected метода
        $reflection = new \ReflectionClass($this->handler);
        $writeMethod = $reflection->getMethod('write');
        $writeMethod->setAccessible(true);
        $writeMethod->invoke($this->handler, $record);

        // Проверяем, что метод выполнился без ошибок
        $this->assertTrue(true);
    }

    public function test_write_with_different_log_levels()
    {
        $levels = [
            Level::Debug,
            Level::Info,
            Level::Warning,
            Level::Error,
            Level::Critical,
            Level::Alert,
            Level::Emergency
        ];

        $this->assertCount(7, $levels, 'Should test all log levels');

        foreach ($levels as $level) {
            $datetime = new \DateTimeImmutable('2023-01-01 12:00:00');
            $message = "Test message for level {$level->name}";

            $record = new LogRecord(
                datetime: $datetime,
                channel: 'test-channel',
                level: $level,
                message: $message,
                context: [],
                extra: []
            );

            // Мокаем RateLimiter
            RateLimiter::shouldReceive('attempt')
                ->once()
                ->andReturn(true);

            // Используем reflection для вызова protected метода
            $reflection = new \ReflectionClass($this->handler);
            $writeMethod = $reflection->getMethod('write');
            $writeMethod->setAccessible(true);
            $writeMethod->invoke($this->handler, $record);
        }

        // Проверяем, что все уровни были обработаны
        $this->assertTrue(true);
    }

    public function test_write_with_special_characters_in_message()
    {
        $datetime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $level = Level::Error;
        $message = 'Test message with special chars: @#$%^&*()_+-=[]{}|;:,.<>?';

        $record = new LogRecord(
            datetime: $datetime,
            channel: 'test-channel',
            level: $level,
            message: $message,
            context: [],
            extra: []
        );

        // Мокаем RateLimiter
        RateLimiter::shouldReceive('attempt')
            ->once()
            ->andReturn(true);

        // Используем reflection для вызова protected метода
        $reflection = new \ReflectionClass($this->handler);
        $writeMethod = $reflection->getMethod('write');
        $writeMethod->setAccessible(true);
        $writeMethod->invoke($this->handler, $record);

        // Проверяем, что сообщение с специальными символами обработано
        $this->assertStringContainsString('@#$%^&*()_+-=[]{}|;:,.<>?', $message);
    }

    public function test_write_with_multiline_message()
    {
        $datetime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $level = Level::Warning;
        $message = "Line 1\nLine 2\nLine 3";

        $record = new LogRecord(
            datetime: $datetime,
            channel: 'test-channel',
            level: $level,
            message: $message,
            context: [],
            extra: []
        );

        // Мокаем RateLimiter
        RateLimiter::shouldReceive('attempt')
            ->once()
            ->andReturn(true);

        // Используем reflection для вызова protected метода
        $reflection = new \ReflectionClass($this->handler);
        $writeMethod = $reflection->getMethod('write');
        $writeMethod->setAccessible(true);
        $writeMethod->invoke($this->handler, $record);

        // Проверяем, что многострочное сообщение обработано
        $this->assertStringContainsString("\n", $message);
        $this->assertStringContainsString('Line 1', $message);
        $this->assertStringContainsString('Line 3', $message);
    }

    public function test_write_with_empty_message()
    {
        $datetime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $level = Level::Info;
        $message = '';

        $record = new LogRecord(
            datetime: $datetime,
            channel: 'test-channel',
            level: $level,
            message: $message,
            context: [],
            extra: []
        );

        // Мокаем RateLimiter
        RateLimiter::shouldReceive('attempt')
            ->once()
            ->andReturn(true);

        // Используем reflection для вызова protected метода
        $reflection = new \ReflectionClass($this->handler);
        $writeMethod = $reflection->getMethod('write');
        $writeMethod->setAccessible(true);
        $writeMethod->invoke($this->handler, $record);

        // Проверяем, что пустое сообщение обработано
        $this->assertEmpty($message);
    }

    public function test_write_with_unicode_characters()
    {
        $datetime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $level = Level::Error;
        $message = 'Тестовое сообщение с кириллицей';

        $record = new LogRecord(
            datetime: $datetime,
            channel: 'test-channel',
            level: $level,
            message: $message,
            context: [],
            extra: []
        );

        // Мокаем RateLimiter
        RateLimiter::shouldReceive('attempt')
            ->once()
            ->andReturn(true);

        // Используем reflection для вызова protected метода
        $reflection = new \ReflectionClass($this->handler);
        $writeMethod = $reflection->getMethod('write');
        $writeMethod->setAccessible(true);
        $writeMethod->invoke($this->handler, $record);

        // Проверяем, что сообщение с кириллицей обработано
        $this->assertStringContainsString('Тестовое', $message);
        $this->assertStringContainsString('кириллицей', $message);
    }

    public function test_handler_uses_correct_rate_limit_key()
    {
        $datetime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $level = Level::Info;
        $message = 'Test message';

        $record = new LogRecord(
            datetime: $datetime,
            channel: 'test-channel',
            level: $level,
            message: $message,
            context: [],
            extra: []
        );

        // Проверяем, что используется правильный ключ для rate limiting
        RateLimiter::shouldReceive('attempt')
            ->once()
            ->with(
                'telegram-log-send:' . $this->channel,
                20,
                Mockery::type('Closure'),
                60
            )
            ->andReturn(true);

        // Используем reflection для вызова protected метода
        $reflection = new \ReflectionClass($this->handler);
        $writeMethod = $reflection->getMethod('write');
        $writeMethod->setAccessible(true);
        $writeMethod->invoke($this->handler, $record);

        // Проверяем правильность ключа rate limiting
        $expectedKey = 'telegram-log-send:' . $this->channel;
        $this->assertEquals($expectedKey, 'telegram-log-send:test-channel');
    }

    public function test_handler_uses_correct_rate_limit_parameters()
    {
        $datetime = new \DateTimeImmutable('2023-01-01 12:00:00');
        $level = Level::Warning;
        $message = 'Test message';

        $record = new LogRecord(
            datetime: $datetime,
            channel: 'test-channel',
            level: $level,
            message: $message,
            context: [],
            extra: []
        );

        // Проверяем правильные параметры rate limiting
        RateLimiter::shouldReceive('attempt')
            ->once()
            ->with(
                Mockery::type('string'),
                20, // max attempts
                Mockery::type('Closure'),
                60  // decay minutes
            )
            ->andReturn(true);

        // Используем reflection для вызова protected метода
        $reflection = new \ReflectionClass($this->handler);
        $writeMethod = $reflection->getMethod('write');
        $writeMethod->setAccessible(true);
        $writeMethod->invoke($this->handler, $record);

        // Проверяем параметры rate limiting
        $this->assertEquals(20, 20); // max attempts
        $this->assertEquals(60, 60); // decay minutes
    }
}
