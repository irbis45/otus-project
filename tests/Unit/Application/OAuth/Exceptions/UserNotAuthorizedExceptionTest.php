<?php

namespace Tests\Unit\Application\OAuth\Exceptions;

use App\Application\OAuth\Exceptions\UserNotAuthorizedException;
use Tests\TestCase;

class UserNotAuthorizedExceptionTest extends TestCase
{
    public function test_exception_extends_base_exception()
    {
        $exception = new UserNotAuthorizedException();
        
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function test_exception_has_correct_message()
    {
        $exception = new UserNotAuthorizedException();
        
        $this->assertEquals('Доступ запрещён: недостаточно прав', $exception->getMessage());
    }

    public function test_exception_has_correct_code()
    {
        $exception = new UserNotAuthorizedException();
        
        $this->assertEquals(403, $exception->getCode());
    }

    public function test_exception_message_is_protected()
    {
        $reflection = new \ReflectionClass(UserNotAuthorizedException::class);
        $messageProperty = $reflection->getProperty('message');
        
        $this->assertTrue($messageProperty->isProtected());
    }

    public function test_exception_code_is_protected()
    {
        $reflection = new \ReflectionClass(UserNotAuthorizedException::class);
        $codeProperty = $reflection->getProperty('code');
        
        $this->assertTrue($codeProperty->isProtected());
    }

    public function test_exception_can_be_thrown_and_caught()
    {
        try {
            throw new UserNotAuthorizedException();
            $this->fail('Exception should have been thrown');
        } catch (UserNotAuthorizedException $e) {
            $this->assertEquals('Доступ запрещён: недостаточно прав', $e->getMessage());
            $this->assertEquals(403, $e->getCode());
        }
    }

    public function test_exception_inheritance_chain()
    {
        $exception = new UserNotAuthorizedException();
        
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(UserNotAuthorizedException::class, $exception);
    }
}
