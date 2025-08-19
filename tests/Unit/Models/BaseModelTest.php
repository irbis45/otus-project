<?php

namespace Tests\Unit\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class BaseModelTest extends TestCase
{
    public function test_base_model_extends_eloquent_model()
    {
        $model = new BaseModel();
        
        $this->assertInstanceOf(Model::class, $model);
    }

    public function test_base_model_has_guarded_property()
    {
        $model = new BaseModel();
        
        $this->assertIsArray($model->getGuarded());
        $this->assertEquals([], $model->getGuarded());
    }

    public function test_base_model_can_be_instantiated()
    {
        $model = new BaseModel();
        
        $this->assertNotNull($model);
        $this->assertInstanceOf(BaseModel::class, $model);
    }
}
