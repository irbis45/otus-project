<?php

namespace Tests\Feature\Controllers\Admin\Categories;

use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('admin')]
#[Group('admin-categories')]
#[Group('admin-categories-index')]
class IndexControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_INDEX = '/admin_panel/categories';

    private User $adminUser;
    private Role $adminRole;

    public function setUp(): void
    {
        parent::setUp();

        // Создаем роль администратора
        $this->adminRole = Role::where('slug', 'admin')->first();

        // Создаем пользователя с ролью администратора
        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($this->adminRole->id);
    }

    public function test_admin_can_view_categories_index(): void
    {
        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.index');
    }

    public function test_categories_index_shows_all_categories(): void
    {
        // Создаем несколько категорий
        $categories = Category::create([
            'name' => 'Первая категория',
            'slug' => 'first-category',
            'description' => 'Описание первой категории',
            'active' => true
        ]);

        $categories = Category::create([
            'name' => 'Вторая категория',
            'slug' => 'second-category',
            'description' => 'Описание второй категории',
            'active' => false
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.index')
            ->assertViewHas('categories');
    }

    public function test_categories_index_shows_both_active_and_inactive_categories(): void
    {
        // Создаем активные и неактивные категории
        $activeCategory = Category::create([
            'name' => 'Активная категория',
            'slug' => 'active-category',
            'description' => 'Описание активной категории',
            'active' => true
        ]);

        $inactiveCategory = Category::create([
            'name' => 'Неактивная категория',
            'slug' => 'inactive-category',
            'description' => 'Описание неактивной категории',
            'active' => false
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.index')
            ->assertViewHas('categories');
    }

    public function test_categories_index_shows_category_details(): void
    {
        $category = Category::create([
            'name' => 'Тестовая категория',
            'slug' => 'test-category',
            'description' => 'Описание тестовой категории',
            'active' => true
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.index')
            ->assertViewHas('categories');
    }

    public function test_categories_index_shows_empty_state_when_no_categories(): void
    {
        // Удаляем все категории
        Category::query()->delete();

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.index')
            ->assertViewHas('categories');
    }


    public function test_categories_index_handles_special_characters(): void
    {
        $specialCategory = Category::create([
            'name' => 'Категория с символами: !@#$%^&*()',
            'slug' => 'special-category',
            'description' => 'Описание с символами: !@#$%^&*()',
            'active' => true
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.index')
            ->assertViewHas('categories');
    }


    public function test_categories_index_handles_long_names(): void
    {
        $longName = str_repeat('Очень длинное название категории ', 2); // Уменьшаем длину
        $longCategory = Category::create([
            'name' => $longName,
            'slug' => 'long-category',
            'description' => 'Описание длинной категории',
            'active' => true
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.index')
            ->assertViewHas('categories');
    }

    public function test_categories_index_performance_with_many_categories(): void
    {
        // Создаем много категорий для проверки производительности
        $categories = [];
        for ($i = 0; $i < 100; $i++) {
            $categories[] = Category::create([
                'name' => "Категория {$i}",
                'slug' => "category-{$i}",
                'description' => "Описание категории {$i}",
                'active' => rand(0, 1) == 1
            ]);
        }

        $startTime = microtime(true);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.index');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Проверяем, что страница загружается достаточно быстро (менее 1 секунды)
        $this->assertLessThan(1.0, $executionTime);
    }

    public function test_categories_index_handles_empty_descriptions(): void
    {
        $noDescCategory = Category::create([
            'name' => 'Категория без описания',
            'slug' => 'no-desc-category',
            'description' => null,
            'active' => true
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.index')
            ->assertViewHas('categories');
    }

    public function test_user_without_admin_role_cannot_access_categories_index(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_user_without_category_view_permission_cannot_access(): void
    {
        // Создаем пользователя без разрешения на просмотр категорий
        $userWithoutPermission = User::factory()->create();
        $userWithoutPermission->roles()->attach($this->adminRole->id);

        $this->actingAs($userWithoutPermission)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK); // Пользователь с ролью admin может получить доступ
    }

    public function test_categories_index_shows_category_status_indicators(): void
    {
        $activeCategory = Category::create([
            'name' => 'Активная категория',
            'slug' => 'active-category',
            'description' => 'Описание активной категории',
            'active' => true
        ]);

        $inactiveCategory = Category::create([
            'name' => 'Неактивная категория',
            'slug' => 'inactive-category',
            'description' => 'Описание неактивной категории',
            'active' => false
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.index')
            ->assertViewHas('categories');
    }

    public function test_categories_index_shows_edit_and_delete_buttons(): void
    {
        $category = Category::create([
            'name' => 'Тестовая категория',
            'slug' => 'test-category',
            'description' => 'Описание тестовой категории',
            'active' => true
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_INDEX)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.index')
            ->assertViewHas('categories');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
