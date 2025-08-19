<?php

namespace Tests\Unit\Application\OAuth\UseCases\Commands\Logout;

use App\Application\OAuth\UseCases\Commands\Logout\Command;
use Tests\TestCase;

class CommandTest extends TestCase
{
    public function test_command_constructor_sets_properties()
    {
        $tokenId = 'token123';

        $command = new Command($tokenId);

        $this->assertEquals($tokenId, $command->tokenId);
    }

    public function test_command_is_readonly()
    {
        $reflection = new \ReflectionClass(Command::class);
        
        $this->assertTrue($reflection->isReadOnly());
    }

    public function test_command_is_final()
    {
        $reflection = new \ReflectionClass(Command::class);
        
        $this->assertTrue($reflection->isFinal());
    }

    public function test_command_properties_are_public()
    {
        $reflection = new \ReflectionClass(Command::class);
        
        $tokenIdProperty = $reflection->getProperty('tokenId');

        $this->assertTrue($tokenIdProperty->isPublic());
    }

    public function test_command_with_empty_string()
    {
        $command = new Command('');

        $this->assertEquals('', $command->tokenId);
    }

    public function test_command_with_special_characters()
    {
        $tokenId = 'token@#$%^&*()_+-=[]{}|;:,.<>?';

        $command = new Command($tokenId);

        $this->assertEquals($tokenId, $command->tokenId);
    }

    public function test_command_with_unicode_characters()
    {
        $tokenId = 'токен123';

        $command = new Command($tokenId);

        $this->assertEquals($tokenId, $command->tokenId);
    }

    public function test_command_with_long_string()
    {
        $tokenId = str_repeat('a', 1000);

        $command = new Command($tokenId);

        $this->assertEquals($tokenId, $command->tokenId);
        $this->assertEquals(1000, strlen($command->tokenId));
    }

    public function test_command_with_numeric_string()
    {
        $tokenId = '123456';

        $command = new Command($tokenId);

        $this->assertEquals($tokenId, $command->tokenId);
    }

    public function test_command_property_is_string()
    {
        $command = new Command('token123');

        $this->assertIsString($command->tokenId);
    }

    public function test_command_with_uuid_format()
    {
        $tokenId = '550e8400-e29b-41d4-a716-446655440000';

        $command = new Command($tokenId);

        $this->assertEquals($tokenId, $command->tokenId);
    }

    public function test_command_with_jwt_format()
    {
        $tokenId = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c';

        $command = new Command($tokenId);

        $this->assertEquals($tokenId, $command->tokenId);
    }

    public function test_command_immutability()
    {
        $command = new Command('token123');
        
        // Попытка изменить свойства должна вызвать ошибку
        $this->expectException(\Error::class);
        
        $command->tokenId = 'newtoken';
    }

    public function test_command_with_whitespace()
    {
        $tokenId = ' token with spaces ';

        $command = new Command($tokenId);

        $this->assertEquals($tokenId, $command->tokenId);
    }

    public function test_command_with_newlines()
    {
        $tokenId = "token\nwith\nnewlines";

        $command = new Command($tokenId);

        $this->assertEquals($tokenId, $command->tokenId);
        $this->assertStringContainsString("\n", $command->tokenId);
    }
}
