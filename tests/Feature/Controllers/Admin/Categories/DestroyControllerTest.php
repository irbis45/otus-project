<?php

namespace Tests\Feature\Controllers\Admin\Categories;

use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\News;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\Controllers\Admin\AdminTestCase;

#[Group('admin')]
#[Group('admin-categories')]
#[Group('admin-categories-destroy')]
class DestroyControllerTest extends AdminTestCase
{
    protected const URL_DELETE = '/admin_panel/categories/%d';

    private User $adminUser;
    private Role $adminRole;
    private Category $category;

    public function setUp(): void
    {
        parent::setUp();

        // Создаем необходимые директории для тестов
        $this->createTestDirectories();

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



    public function test_admin_can_delete_category(): void
    {
        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $this->category->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/categories');

        $this->assertDatabaseMissing('categories', ['id' => $this->category->id]);
    }

    public function test_delete_category_with_news(): void
    {
        // Создаем новости в категории
        $news = News::factory(5)->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'active' => true,
            'published_at' => now()->subDays(rand(1, 10)),
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $this->category->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/categories');

        $this->assertDatabaseMissing('categories', ['id' => $this->category->id]);
        // Проверяем, что новости остались, но category_id стал null
        foreach ($news as $newsItem) {
            $this->assertDatabaseHas('news', ['id' => $newsItem->id]);
            $this->assertDatabaseHas('news', [
                'id' => $newsItem->id,
                'category_id' => null
            ]);
        }
    }

    public function test_delete_inactive_category(): void
    {
        $inactiveCategory = Category::factory()->create([
            'name' => 'Неактивная категория',
            'slug' => 'inactive-category',
            'description' => 'Описание неактивной категории',
            'active' => false,
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $inactiveCategory->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/categories');

        $this->assertDatabaseMissing('categories', ['id' => $inactiveCategory->id]);
    }

    public function test_delete_category_without_description(): void
    {
        $categoryWithoutDescription = Category::factory()->create([
            'name' => 'Категория без описания',
            'slug' => 'no-description-category',
            'description' => null,
            'active' => true,
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $categoryWithoutDescription->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/categories');

        $this->assertDatabaseMissing('categories', ['id' => $categoryWithoutDescription->id]);
    }

    public function test_delete_category_with_long_name(): void
    {
        $longName = str_repeat('Очень длинное название категории ', 2); // Уменьшаем длину еще больше
        $categoryWithLongName = Category::factory()->create([
            'name' => $longName,
            'slug' => 'long-name-category',
            'description' => 'Описание',
            'active' => true,
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $categoryWithLongName->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/categories');

        $this->assertDatabaseMissing('categories', ['id' => $categoryWithLongName->id]);
    }

    public function test_delete_category_with_special_characters(): void
    {
        $categoryWithSpecialChars = Category::factory()->create([
            'name' => 'Категория с символами: @#$%^&*()',
            'slug' => 'special-chars-category',
            'description' => 'Описание с символами: <>&"\'',
            'active' => true,
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $categoryWithSpecialChars->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/categories');

        $this->assertDatabaseMissing('categories', ['id' => $categoryWithSpecialChars->id]);
    }

    public function test_delete_returns_404_for_nonexistent_category(): void
    {
        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, 99999))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_guest_cannot_delete_category(): void
    {
        $this->delete(sprintf(self::URL_DELETE, $this->category->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/login');

        $this->assertDatabaseHas('categories', ['id' => $this->category->id]);
    }

    public function test_user_without_admin_role_cannot_delete_category(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->delete(sprintf(self::URL_DELETE, $this->category->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertDatabaseHas('categories', ['id' => $this->category->id]);
    }

    public function test_delete_category_with_many_news(): void
    {
        // Создаем много новостей в категории
        $news = News::factory(50)->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'active' => true,
            'published_at' => now()->subDays(rand(1, 30)),
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $this->category->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/categories');

        $this->assertDatabaseMissing('categories', ['id' => $this->category->id]);
        // Проверяем, что новости остались, но category_id стал null
        foreach ($news as $newsItem) {
            $this->assertDatabaseHas('news', ['id' => $newsItem->id]);
            $this->assertDatabaseHas('news', [
                'id' => $newsItem->id,
                'category_id' => null
            ]);
        }
    }

    public function test_delete_category_with_featured_news(): void
    {
        // Создаем избранные новости в категории
        $featuredNews = News::factory(3)->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'active' => true,
            'featured' => true,
            'published_at' => now()->subDays(rand(1, 10)),
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $this->category->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/categories');

        $this->assertDatabaseMissing('categories', ['id' => $this->category->id]);
        // Проверяем, что избранные новости остались, но category_id стал null
        foreach ($featuredNews as $newsItem) {
            $this->assertDatabaseHas('news', ['id' => $newsItem->id]);
            $this->assertDatabaseHas('news', [
                'id' => $newsItem->id,
                'category_id' => null
            ]);
        }
    }

    public function test_delete_category_with_popular_news(): void
    {
        // Создаем популярные новости с высоким количеством просмотров
        $popularNews = News::factory(10)->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'active' => true,
            'views' => rand(1000, 10000),
            'published_at' => now()->subDays(rand(1, 20)),
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $this->category->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/categories');

        $this->assertDatabaseMissing('categories', ['id' => $this->category->id]);
        // Проверяем, что популярные новости остались, но category_id стал null
        foreach ($popularNews as $newsItem) {
            $this->assertDatabaseHas('news', ['id' => $newsItem->id]);
            $this->assertDatabaseHas('news', [
                'id' => $newsItem->id,
                'category_id' => null
            ]);
        }
    }

    public function test_delete_category_with_recent_news(): void
    {
        // Создаем недавние новости
        $recentNews = News::factory(8)->create([
            'author_id' => $this->adminUser->id,
            'category_id' => $this->category->id,
            'active' => true,
            'published_at' => now()->subHours(rand(1, 24)),
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $this->category->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/categories');

        $this->assertDatabaseMissing('categories', ['id' => $this->category->id]);
        // Проверяем, что недавние новости остались, но category_id стал null
        foreach ($recentNews as $newsItem) {
            $this->assertDatabaseHas('news', ['id' => $newsItem->id]);
            $this->assertDatabaseHas('news', [
                'id' => $newsItem->id,
                'category_id' => null
            ]);
        }
    }

    public function test_delete_category_success_message(): void
    {
        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $this->category->id))
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/admin_panel/categories')
            ->assertSessionHas('success');
    }

    public function test_multiple_categories_deletion(): void
    {
        $category1 = Category::factory()->create([
            'name' => 'Категория 1',
            'slug' => 'category-1',
        ]);

        $category2 = Category::factory()->create([
            'name' => 'Категория 2',
            'slug' => 'category-2',
        ]);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $category1->id))
            ->assertStatus(Response::HTTP_FOUND);

        $this->actingAs($this->adminUser)
            ->delete(sprintf(self::URL_DELETE, $category2->id))
            ->assertStatus(Response::HTTP_FOUND);

        $this->assertDatabaseMissing('categories', ['id' => $category1->id]);
        $this->assertDatabaseMissing('categories', ['id' => $category2->id]);
    }


}
