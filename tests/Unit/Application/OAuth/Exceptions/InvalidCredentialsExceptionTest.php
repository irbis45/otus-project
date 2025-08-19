<?php

namespace Tests\Unit\Application\OAuth\Exceptions;

use App\Application\OAuth\Exceptions\InvalidCredentialsException;
use Tests\TestCase;

class InvalidCredentialsExceptionTest extends TestCase
{
    public function test_exception_extends_base_exception()
    {
        $exception = new InvalidCredentialsException();
        
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function test_exception_is_final()
    {
        $reflection = new \ReflectionClass(InvalidCredentialsException::class);
        
        $this->assertTrue($reflection->isFinal());
    }

    public function test_exception_has_default_message()
    {
        $exception = new InvalidCredentialsException();
        
        $this->assertEquals('Invalid credential', $exception->getMessage());
    }

    public function test_exception_accepts_custom_message()
    {
        $customMessage = 'Custom invalid credentials message';
        $exception = new InvalidCredentialsException($customMessage);
        
        $this->assertEquals($customMessage, $exception->getMessage());
    }

    public function test_exception_with_empty_message()
    {
        $exception = new InvalidCredentialsException('');
        
        $this->assertEquals('', $exception->getMessage());
    }

    public function test_exception_with_special_characters_in_message()
    {
        $message = 'Error with special chars: @#$%^&*()_+-=[]{}|;:,.<>?';
        $exception = new InvalidCredentialsException($message);
        
        $this->assertEquals($message, $exception->getMessage());
    }

    public function test_exception_with_unicode_message()
    {
        $message = 'Ошибка с кириллицей';
        $exception = new InvalidCredentialsException($message);
        
        $this->assertEquals($message, $exception->getMessage());
    }

    public function test_exception_with_long_message()
    {
        $message = str_repeat('Very long error message. ', 50);
        $exception = new InvalidCredentialsException($message);
        
        $this->assertEquals($message, $exception->getMessage());
        $this->assertGreaterThan(1000, strlen($exception->getMessage()));
    }

    public function test_exception_can_be_thrown_and_caught()
    {
        try {
            throw new InvalidCredentialsException('Test error');
            $this->fail('Exception should have been thrown');
        } catch (InvalidCredentialsException $e) {
            $this->assertEquals('Test error', $e->getMessage());
        }
    }

    public function test_exception_inheritance_chain()
    {
        $exception = new InvalidCredentialsException();
        
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(InvalidCredentialsException::class, $exception);
    }

    public function test_exception_constructor_calls_parent()
    {
        $exception = new InvalidCredentialsException('Test message');
        
        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
    }

    public function test_exception_with_numeric_message()
    {
        $message = '12345';
        $exception = new InvalidCredentialsException($message);
        
        $this->assertEquals($message, $exception->getMessage());
    }

    public function test_exception_with_html_message()
    {
        $message = '<strong>HTML</strong> error message';
        $exception = new InvalidCredentialsException($message);
        
        $this->assertEquals($message, $exception->getMessage());
    }
}
