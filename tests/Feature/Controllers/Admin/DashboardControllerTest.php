<?php

namespace Tests\Feature\Controllers\Admin;

use App\Application\Core\Category\Repositories\CategoryRepositoryInterface;
use App\Application\Core\Comment\Repositories\CommentRepositoryInterface;
use App\Application\Core\News\Repositories\NewsRepositoryInterface;
use App\Application\Core\User\Repositories\UserRepositoryInterface;
use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\News;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('admin')]
#[Group('admin-dashboard')]
class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_DASHBOARD = '/admin_panel';

    private User $adminUser;
    private Role $adminRole;

    public function setUp(): void
    {
        parent::setUp();

        // Получаем роль администратора из базы данных
        $this->adminRole = Role::where('slug', 'admin')->first();

        // Создаем пользователя с ролью администратора
        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($this->adminRole->id);
    }

    public function test_admin_can_access_dashboard(): void
    {
        $this->actingAs($this->adminUser)
            ->get(self::URL_DASHBOARD)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.dashboard');
    }

    public function test_dashboard_shows_correct_statistics(): void
    {
        // Получаем текущее количество записей
        $currentUsersCount = User::count();
        $currentCategoriesCount = Category::count();
        $currentNewsCount = News::count();
        $currentCommentsCount = Comment::count();
        
        // Создаем тестовые данные
        $users = User::factory(5)->create();
        $categories = Category::factory(3)->create();
        $news = News::factory(7)->create([
            'author_id' => $this->adminUser->id,
            'category_id' => fn() => Category::inRandomOrder()->first()->id,
        ]);
        $comments = Comment::factory(12)->create([
            'author_id' => fn() => User::inRandomOrder()->first()->id,
            'news_id' => fn() => News::inRandomOrder()->first()->id,
        ]);

        $this->actingAs($this->adminUser)
            ->get(self::URL_DASHBOARD)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('admin.dashboard')
            ->assertViewHas('totalUsers', $currentUsersCount + 5)
            ->assertViewHas('totalCategories', $currentCategoriesCount + 3)
            ->assertViewHas('totalNews', $currentNewsCount + 7)
            ->assertViewHas('totalComments', $currentCommentsCount + 12);
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get(self::URL_DASHBOARD)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/login');
    }

    public function test_user_without_admin_role_cannot_access_dashboard(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->get(self::URL_DASHBOARD)
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
