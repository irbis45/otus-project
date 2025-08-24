<?php

namespace Tests\Unit\Application\OAuth\UseCases\Commands\Login;

use App\Application\OAuth\UseCases\Commands\Login\Command;
use Tests\TestCase;

class CommandTest extends TestCase
{
    public function test_command_constructor_sets_properties()
    {
        $email = 'test@example.com';
        $password = 'password123';

        $command = new Command($email, $password);

        $this->assertEquals($email, $command->email);
        $this->assertEquals($password, $command->password);
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

        $emailProperty = $reflection->getProperty('email');
        $passwordProperty = $reflection->getProperty('password');

        $this->assertTrue($emailProperty->isPublic());
        $this->assertTrue($passwordProperty->isPublic());
    }

    public function test_command_with_empty_strings()
    {
        $command = new Command('', '');

        $this->assertEquals('', $command->email);
        $this->assertEquals('', $command->password);
    }

    public function test_command_with_special_characters()
    {
        $email = 'test+tag@example.com';
        $password = 'p@ssw0rd!@#$%^&*()';

        $command = new Command($email, $password);

        $this->assertEquals($email, $command->email);
        $this->assertEquals($password, $command->password);
    }

    public function test_command_with_unicode_characters()
    {
        $email = 'тест@example.com';
        $password = 'пароль123';

        $command = new Command($email, $password);

        $this->assertEquals($email, $command->email);
        $this->assertEquals($password, $command->password);
    }

    public function test_command_with_long_strings()
    {
        $email = str_repeat('a', 100) . '@example.com';
        $password = str_repeat('b', 100);

        $command = new Command($email, $password);

        $this->assertEquals($email, $command->email);
        $this->assertEquals($password, $command->password);
    }

    public function test_command_with_numeric_strings()
    {
        $email = '123@example.com';
        $password = '123456';

        $command = new Command($email, $password);

        $this->assertEquals($email, $command->email);
        $this->assertEquals($password, $command->password);
    }

    public function test_command_properties_are_strings()
    {
        $command = new Command('test@example.com', 'password');

        $this->assertIsString($command->email);
        $this->assertIsString($command->password);
    }
}



