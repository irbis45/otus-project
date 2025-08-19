<?php

namespace Tests\Unit\Application\OAuth\Exceptions;

use App\Application\OAuth\Exceptions\UserNotFoundException;
use Tests\TestCase;

class UserNotFoundExceptionTest extends TestCase
{
    public function test_exception_extends_base_exception()
    {
        $exception = new UserNotFoundException();
        
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function test_exception_is_final()
    {
        $reflection = new \ReflectionClass(UserNotFoundException::class);
        
        $this->assertTrue($reflection->isFinal());
    }

    public function test_exception_can_be_thrown_and_caught()
    {
        try {
            throw new UserNotFoundException();
            $this->fail('Exception should have been thrown');
        } catch (UserNotFoundException $e) {
            $this->assertInstanceOf(UserNotFoundException::class, $e);
        }
    }

    public function test_exception_inheritance_chain()
    {
        $exception = new UserNotFoundException();
        
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(UserNotFoundException::class, $exception);
    }

    public function test_exception_has_default_message()
    {
        $exception = new UserNotFoundException();
        
        $this->assertEquals('', $exception->getMessage());
    }

    public function test_exception_has_default_code()
    {
        $exception = new UserNotFoundException();
        
        $this->assertEquals(0, $exception->getCode());
    }
}
