<?php

namespace Tests\Feature\Controllers\Public;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('public')]
#[Group('public-profile')]
class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected const URL_PROFILE_EDIT = '/profile';
    protected const URL_PROFILE_UPDATE = '/profile';
    protected const URL_PROFILE_DESTROY = '/profile';
    protected const URL_HOME = '/';

    private User $user;
    private string $password;

    public function setUp(): void
    {
        parent::setUp();

        $this->password = 'password123';
        $this->user = User::factory()->create([
            'password' => Hash::make($this->password),
        ]);
    }

    public function test_authenticated_user_can_view_profile_edit_form(): void
    {
        $this->actingAs($this->user)
            ->get(self::URL_PROFILE_EDIT)
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('profile.edit');
    }

    public function test_guest_cannot_access_profile_edit_form(): void
    {
        $this->get(self::URL_PROFILE_EDIT)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/login');
    }

    public function test_authenticated_user_can_update_profile_with_valid_data(): void
    {
        $updateData = [
            'name' => 'Обновленное Имя',
            'email' => 'updated@example.com',
        ];

        $this->actingAs($this->user)
            ->put(self::URL_PROFILE_UPDATE, $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect(self::URL_PROFILE_EDIT)
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Обновленное Имя',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_authenticated_user_can_update_profile_with_password_change(): void
    {
        $newPassword = 'newpassword123';
        $updateData = [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'current_password' => $this->password,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ];

        $this->actingAs($this->user)
            ->put(self::URL_PROFILE_UPDATE, $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect(self::URL_PROFILE_EDIT)
            ->assertSessionHas('success');

        $this->user->refresh();
        $this->assertTrue(Hash::check($newPassword, $this->user->password));
    }

    public function test_profile_update_validates_required_fields(): void
    {
        $this->actingAs($this->user)
            ->put(self::URL_PROFILE_UPDATE, [])
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['name', 'email']);
    }

    public function test_profile_update_validates_email_format(): void
    {
        $updateData = [
            'name' => $this->user->name,
            'email' => 'invalid-email',
        ];

        $this->actingAs($this->user)
            ->put(self::URL_PROFILE_UPDATE, $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['email']);
    }

    public function test_profile_update_validates_email_uniqueness(): void
    {
        $otherUser = User::factory()->create(['email' => 'other@example.com']);

        $updateData = [
            'name' => $this->user->name,
            'email' => 'other@example.com',
        ];

        $this->actingAs($this->user)
            ->put(self::URL_PROFILE_UPDATE, $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['email']);
    }

    public function test_profile_update_allows_same_email_for_same_user(): void
    {
        $updateData = [
            'name' => 'Обновленное Имя',
            'email' => $this->user->email, // Тот же email
        ];

        $this->actingAs($this->user)
            ->put(self::URL_PROFILE_UPDATE, $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect(self::URL_PROFILE_EDIT)
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Обновленное Имя',
            'email' => $this->user->email,
        ]);
    }

    public function test_profile_update_validates_current_password(): void
    {
        $updateData = [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        $this->actingAs($this->user)
            ->put(self::URL_PROFILE_UPDATE, $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_profile_update_validates_password_length(): void
    {
        $updateData = [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'current_password' => $this->password,
            'password' => '123', // Слишком короткий пароль
            'password_confirmation' => '123',
        ];

        $this->actingAs($this->user)
            ->put(self::URL_PROFILE_UPDATE, $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertSessionHasErrors(['password']);
    }

    public function test_guest_cannot_update_profile(): void
    {
        $updateData = [
            'name' => 'Обновленное Имя',
            'email' => 'updated@example.com',
        ];

        $this->put(self::URL_PROFILE_UPDATE, $updateData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/login');
    }

    public function test_authenticated_user_can_delete_profile(): void
    {
        $deleteData = [
            'password' => $this->password,
        ];

        $this->actingAs($this->user)
            ->delete(self::URL_PROFILE_DESTROY, $deleteData)
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect(self::URL_HOME);

        $this->assertDatabaseMissing('users', ['id' => $this->user->id]);
    }

    public function test_profile_update_without_password_change_keeps_old_password(): void
    {
        $originalPasswordHash = $this->user->password;

        $updateData = [
            'name' => 'Обновленное Имя',
            'email' => 'updated@example.com',
        ];

        $this->actingAs($this->user)
            ->put(self::URL_PROFILE_UPDATE, $updateData)
            ->assertStatus(Response::HTTP_FOUND);

        $this->user->refresh();
        $this->assertEquals($originalPasswordHash, $this->user->password);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
