<?php

namespace Tests\Unit\Application\OAuth\Exceptions;

use App\Application\OAuth\Exceptions\UserSaveException;
use Tests\TestCase;

class UserSaveExceptionTest extends TestCase
{
    public function test_exception_extends_base_exception()
    {
        $exception = new UserSaveException();
        
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function test_exception_is_final()
    {
        $reflection = new \ReflectionClass(UserSaveException::class);
        
        $this->assertTrue($reflection->isFinal());
    }

    public function test_exception_has_default_message()
    {
        $exception = new UserSaveException();
        
        $this->assertEquals('Не удалось сохранить пользователя', $exception->getMessage());
    }

    public function test_exception_accepts_custom_message()
    {
        $customMessage = 'Custom error message';
        $exception = new UserSaveException($customMessage);
        
        $this->assertEquals($customMessage, $exception->getMessage());
    }

    public function test_exception_with_empty_message()
    {
        $exception = new UserSaveException('');
        
        $this->assertEquals('', $exception->getMessage());
    }

    public function test_exception_with_special_characters_in_message()
    {
        $message = 'Error with special chars: @#$%^&*()_+-=[]{}|;:,.<>?';
        $exception = new UserSaveException($message);
        
        $this->assertEquals($message, $exception->getMessage());
    }

    public function test_exception_with_unicode_message()
    {
        $message = 'Ошибка с кириллицей';
        $exception = new UserSaveException($message);
        
        $this->assertEquals($message, $exception->getMessage());
    }

    public function test_exception_with_long_message()
    {
        $message = str_repeat('Very long error message. ', 50);
        $exception = new UserSaveException($message);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertGreaterThan(1000, strlen($exception->getMessage()));
    }

    public function test_exception_can_be_thrown_and_caught()
    {
        try {
            throw new UserSaveException('Test error');
            $this->fail('Exception should have been thrown');
        } catch (UserSaveException $e) {
            $this->assertEquals('Test error', $e->getMessage());
        }
    }

    public function test_exception_inheritance_chain()
    {
        $exception = new UserSaveException();
        
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(UserSaveException::class, $exception);
    }

    public function test_exception_constructor_calls_parent()
    {
        $exception = new UserSaveException('Test message');
        
        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
    }
}
