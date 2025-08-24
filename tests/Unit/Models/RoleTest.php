<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Tests\TestCase;

class RoleTest extends TestCase
{
    public function test_role_extends_base_model()
    {
        $role = new Role();
        $this->assertInstanceOf(\App\Models\BaseModel::class, $role);
    }

    public function test_role_has_fillable_attributes()
    {
        $role = new Role();
        $expectedFillable = ['slug', 'name'];
        $this->assertEquals($expectedFillable, $role->getFillable());
    }

    public function test_get_column_name_returns_mapped_value()
    {
        $role = new Role();
        $role->id = 1;
        $role->name = 'Admin Role';

        $this->assertEquals('id', $role->getColumnName('id'));
        $this->assertEquals('name', $role->getColumnName('name'));
        $this->assertEquals('unknown', $role->getColumnName('unknown'));
    }

    public function test_get_id_returns_role_id()
    {
        $role = new Role();
        $role->id = 123;
        $this->assertEquals(123, $role->getId());
    }

    public function test_get_slug_returns_role_slug()
    {
        $role = new Role();
        $role->slug = 'admin';
        $this->assertEquals('admin', $role->getSlug());
    }

    public function test_role_has_users_relationship()
    {
        $role = new Role();
        $this->assertTrue(method_exists($role, 'users'));
    }

    public function test_role_has_permissions_relationship()
    {
        $role = new Role();
        $this->assertTrue(method_exists($role, 'permissions'));
    }

    public function test_role_timestamps_disabled()
    {
        $role = new Role();
        $this->assertFalse($role->timestamps);
    }

    public function test_role_users_relationship_returns_belongs_to_many()
    {
        $role = new Role();
        $relationship = $role->users();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $relationship);
    }

    public function test_role_permissions_relationship_returns_belongs_to_many()
    {
        $role = new Role();
        $relationship = $role->permissions();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $relationship);
    }

    public function test_role_permissions_relationship_uses_custom_pivot_table()
    {
        $role = new Role();
        $relationship = $role->permissions();
        $this->assertEquals('permission_role', $relationship->getTable());
    }
}
