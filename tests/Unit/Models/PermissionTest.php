<?php

namespace Tests\Unit\Models;

use App\Models\Permission;
use App\Models\Role;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    public function test_permission_extends_eloquent_model()
    {
        $permission = new Permission();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Model::class, $permission);
    }

    public function test_permission_has_fillable_attributes()
    {
        $permission = new Permission();
        $expectedFillable = ['slug', 'name'];
        $this->assertEquals($expectedFillable, $permission->getFillable());
    }

    public function test_get_column_name_returns_mapped_value()
    {
        $permission = new Permission();
        $permission->id = 1;
        $permission->name = 'Create News';
        
        $this->assertEquals('id', $permission->getColumnName('id'));
        $this->assertEquals('name', $permission->getColumnName('name'));
        $this->assertEquals('unknown', $permission->getColumnName('unknown'));
    }

    public function test_get_id_returns_permission_id()
    {
        $permission = new Permission();
        $permission->id = 123;
        $this->assertEquals(123, $permission->getId());
    }

    public function test_get_slug_returns_permission_slug()
    {
        $permission = new Permission();
        $permission->slug = 'create_news';
        $this->assertEquals('create_news', $permission->getSlug());
    }

    public function test_get_name_returns_permission_name()
    {
        $permission = new Permission();
        $permission->name = 'Create News';
        $this->assertEquals('Create News', $permission->getName());
    }

    public function test_permission_has_roles_relationship()
    {
        $permission = new Permission();
        $this->assertTrue(method_exists($permission, 'roles'));
    }

    public function test_permission_timestamps_disabled()
    {
        $permission = new Permission();
        $this->assertFalse($permission->timestamps);
    }

    public function test_permission_roles_relationship_returns_belongs_to_many()
    {
        $permission = new Permission();
        $relationship = $permission->roles();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $relationship);
    }

    public function test_permission_roles_relationship_uses_custom_pivot_table()
    {
        $permission = new Permission();
        $relationship = $permission->roles();
        $this->assertEquals('permission_role', $relationship->getTable());
    }
}
