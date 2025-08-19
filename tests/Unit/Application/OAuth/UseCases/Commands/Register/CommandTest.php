<?php

namespace Tests\Unit\Application\OAuth\UseCases\Commands\Register;

use App\Application\OAuth\UseCases\Commands\Register\Command;
use Tests\TestCase;

class CommandTest extends TestCase
{
    public function test_command_constructor_sets_properties()
    {
        $name = 'John Doe';
        $email = 'john@example.com';
        $password = 'password123';

        $command = new Command($name, $email, $password);

        $this->assertEquals($name, $command->name);
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
        
        $nameProperty = $reflection->getProperty('name');
        $emailProperty = $reflection->getProperty('email');
        $passwordProperty = $reflection->getProperty('password');

        $this->assertTrue($nameProperty->isPublic());
        $this->assertTrue($emailProperty->isPublic());
        $this->assertTrue($passwordProperty->isPublic());
    }

    public function test_command_with_empty_strings()
    {
        $command = new Command('', '', '');

        $this->assertEquals('', $command->name);
        $this->assertEquals('', $command->email);
        $this->assertEquals('', $command->password);
    }

    public function test_command_with_special_characters()
    {
        $name = 'John Doe Jr.';
        $email = 'john+tag@example.com';
        $password = 'p@ssw0rd!@#$%^&*()';

        $command = new Command($name, $email, $password);

        $this->assertEquals($name, $command->name);
        $this->assertEquals($email, $command->email);
        $this->assertEquals($password, $command->password);
    }

    public function test_command_with_unicode_characters()
    {
        $name = 'Иван Петров';
        $email = 'иван@example.com';
        $password = 'пароль123';

        $command = new Command($name, $email, $password);

        $this->assertEquals($name, $command->name);
        $this->assertEquals($email, $command->email);
        $this->assertEquals($password, $command->password);
    }

    public function test_command_with_long_strings()
    {
        $name = str_repeat('A', 100);
        $email = str_repeat('a', 100) . '@example.com';
        $password = str_repeat('b', 100);

        $command = new Command($name, $email, $password);

        $this->assertEquals($name, $command->name);
        $this->assertEquals($email, $command->email);
        $this->assertEquals($password, $command->password);
    }

    public function test_command_with_numeric_strings()
    {
        $name = '123';
        $email = '123@example.com';
        $password = '123456';

        $command = new Command($name, $email, $password);

        $this->assertEquals($name, $command->name);
        $this->assertEquals($email, $command->email);
        $this->assertEquals($password, $command->password);
    }

    public function test_command_properties_are_strings()
    {
        $command = new Command('John', 'john@example.com', 'password');

        $this->assertIsString($command->name);
        $this->assertIsString($command->email);
        $this->assertIsString($command->password);
    }

    public function test_command_with_html_in_name()
    {
        $name = '<strong>John</strong> Doe';
        $email = 'john@example.com';
        $password = 'password';

        $command = new Command($name, $email, $password);

        $this->assertEquals($name, $command->name);
        $this->assertStringContainsString('<strong>', $command->name);
    }

    public function test_command_with_multiline_name()
    {
        $name = "John\nDoe";
        $email = 'john@example.com';
        $password = 'password';

        $command = new Command($name, $email, $password);

        $this->assertEquals($name, $command->name);
        $this->assertStringContainsString("\n", $command->name);
    }

    public function test_command_immutability()
    {
        $command = new Command('John', 'john@example.com', 'password');
        
        // Попытка изменить свойства должна вызвать ошибку
        $this->expectException(\Error::class);
        
        $command->name = 'Jane';
    }
}
