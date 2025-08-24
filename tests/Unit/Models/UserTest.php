<?php

namespace Tests\Unit\Models;

use App\Application\Core\Permission\Enums\Permission as PermissionEnum;
use App\Application\Core\Role\Enums\Role as RoleEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;

class UserTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_user_has_fillable_attributes()
    {
        $user = new User();

        $expectedFillable = ['name', 'email', 'password'];
        $this->assertEquals($expectedFillable, $user->getFillable());
    }

    public function test_user_has_hidden_attributes()
    {
        $user = new User();

        $expectedHidden = ['password', 'remember_token'];
        $this->assertEquals($expectedHidden, $user->getHidden());
    }

    public function test_get_column_name_returns_mapped_value()
    {
        $user = new User();
        $user->id = 1;
        $user->name = 'John Doe';

        $this->assertEquals('id', $user->getColumnName('id'));
        $this->assertEquals('name', $user->getColumnName('name'));
        $this->assertEquals('unknown', $user->getColumnName('unknown'));
    }

    public function test_get_id_returns_user_id()
    {
        $user = new User();
        $user->id = 123;

        $this->assertEquals(123, $user->getId());
    }

    public function test_get_name_returns_user_name()
    {
        $user = new User();
        $user->name = 'John Doe';

        $this->assertEquals('John Doe', $user->getName());
    }

    public function test_get_email_returns_user_email()
    {
        $user = new User();
        $user->email = 'john@example.com';

        $this->assertEquals('john@example.com', $user->getEmail());
    }

    public function test_get_email_verified_at_returns_carbon_instance()
    {
        $user = new User();
        $user->email_verified_at = '2023-01-01 12:00:00';

        $result = $user->getEmailVerifiedAt();
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('2023-01-01 12:00:00', $result->toDateTimeString());
    }

    public function test_get_email_verified_at_returns_null_when_not_set()
    {
        $user = new User();

        $this->assertNull($user->getEmailVerifiedAt());
    }

    public function test_get_created_at_returns_carbon_instance()
    {
        $user = new User();
        $user->created_at = '2023-01-01 12:00:00';

        $result = $user->getCreatedAt();
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('2023-01-01 12:00:00', $result->toDateTimeString());
    }

    public function test_get_updated_at_returns_carbon_instance()
    {
        $user = new User();
        $user->updated_at = '2023-01-01 12:00:00';

        $result = $user->getUpdatedAt();
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('2023-01-01 12:00:00', $result->toDateTimeString());
    }

    public function test_user_has_news_relationship()
    {
        $user = new User();

        $this->assertTrue(method_exists($user, 'news'));
    }

    public function test_user_has_comments_relationship()
    {
        $user = new User();

        $this->assertTrue(method_exists($user, 'comments'));
    }

    public function test_user_has_roles_relationship()
    {
        $user = new User();

        $this->assertTrue(method_exists($user, 'roles'));
    }

    public function test_user_uses_has_api_tokens_trait()
    {
        $user = new User();

        $this->assertTrue(method_exists($user, 'tokens'));
    }

    public function test_user_uses_notifiable_trait()
    {
        $user = new User();

        $this->assertTrue(method_exists($user, 'notify'));
    }

    public function test_user_has_has_permission_method()
    {
        $user = new User();

        $this->assertTrue(method_exists($user, 'hasPermission'));
    }

    public function test_user_has_sync_roles_method()
    {
        $user = new User();

        $this->assertTrue(method_exists($user, 'syncRoles'));
    }
}
