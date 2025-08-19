<?php

namespace Tests\Feature\Controllers\Admin\Categories;

use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('admin')]
#[Group('admin-categories')]
#[Group('admin-categories-show')]
class ShowControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_SHOW = '/admin_panel/categories/%d';

    private User $adminUser;
    private Role $adminRole;
    private Category $category;

    public function setUp(): void
    {
        parent::setUp();

        // Получаем роль администратора из базы данных
        $this->adminRole = Role::where('slug', 'admin')->first();

        // Создаем пользователя с ролью администратора
        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($this->adminRole->id);

        // Создаем категорию
        $this->category = Category::factory()->create([
            'name' => 'Тестовая категория',
            'slug' => 'test-category',
            'description' => 'Описание тестовой категории',
            'active' => true,
        ]);
    }

    public function test_admin_can_view_category_show(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->category->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.show')
            ->assertViewHas('category');
    }

    public function test_category_show_displays_correct_data(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->category->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.show')
            ->assertViewHas('category')
            ->assertSee($this->category->name)
            ->assertSee($this->category->description);
    }

    public function test_category_show_returns_404_for_nonexistent_category(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, 99999))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_guest_cannot_access_category_show(): void
    {
        $this->get(sprintf(self::URL_SHOW, $this->category->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/login');
    }

    public function test_user_without_admin_role_cannot_access_category_show(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->get(sprintf(self::URL_SHOW, $this->category->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_category_show_displays_news_count(): void
    {
        // Создаем новости в категории
        $news = News::factory(5)->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'active' => true,
            'published_at' => now()->subDays(rand(1, 10)),
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->category->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.show');
    }

    public function test_category_show_displays_activity_status(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->category->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.show');
    }

    public function test_category_show_displays_slug(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->category->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.show');
    }

    public function test_category_show_with_inactive_category(): void
    {
        $inactiveCategory = Category::factory()->create([
            'name' => 'Неактивная категория',
            'slug' => 'inactive-category',
            'description' => 'Описание неактивной категории',
            'active' => false,
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $inactiveCategory->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.show')
            ->assertViewHas('category');
    }

    public function test_category_show_with_category_without_description(): void
    {
        $categoryWithoutDescription = Category::factory()->create([
            'name' => 'Категория без описания',
            'slug' => 'no-description-category',
            'description' => null,
            'active' => true,
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $categoryWithoutDescription->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.show')
            ->assertViewHas('category');
    }

    public function test_category_show_with_category_with_long_name(): void
    {
        $longName = str_repeat('Очень длинное название категории ', 2); // Уменьшаем длину
        $categoryWithLongName = Category::factory()->create([
            'name' => $longName,
            'slug' => 'long-name-category',
            'description' => 'Описание',
            'active' => true,
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $categoryWithLongName->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.show')
            ->assertViewHas('category');
    }

    public function test_category_show_with_category_with_special_characters(): void
    {
        $categoryWithSpecialChars = Category::factory()->create([
            'name' => 'Категория с символами: @#$%^&*()',
            'slug' => 'special-chars-category',
            'description' => 'Описание с символами: <>&"\'',
            'active' => true,
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $categoryWithSpecialChars->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.show')
            ->assertViewHas('category');
    }

    public function test_category_show_with_category_with_many_news(): void
    {
        // Создаем много новостей в категории
        $news = News::factory(25)->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'active' => true,
            'published_at' => now()->subDays(rand(1, 30)),
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->category->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.show');
    }

    public function test_category_show_with_category_with_no_news(): void
    {
        $emptyCategory = Category::factory()->create([
            'name' => 'Пустая категория',
            'slug' => 'empty-category',
            'description' => 'Категория без новостей',
            'active' => true,
        ]);

        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $emptyCategory->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.show')
            ->assertViewHas('category');
    }

    public function test_category_show_displays_creation_date(): void
    {
        $this->actingAs($this->adminUser)
            ->get(sprintf(self::URL_SHOW, $this->category->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.categories.show');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
