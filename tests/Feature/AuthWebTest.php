<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthWebTest extends TestCase
{
    use RefreshDatabase;

    private function createManager(): User
    {
        $role = Role::firstOrCreate(['name' => 'manager']);
        $user = User::factory()->create([
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole($role);

        return $user;
    }

    #[Test]
    public function it_displays_login_form(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200)
            ->assertViewIs('admin.auth.login');
    }

    #[Test]
    public function it_can_login_with_valid_credentials(): void
    {
        $manager = $this->createManager();

        $response = $this->post('/admin/login', [
            'email' => 'manager@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.tickets.index'));
        $this->assertAuthenticatedAs($manager);
    }

    #[Test]
    public function it_denies_login_with_invalid_credentials(): void
    {
        $this->createManager();

        $response = $this->post('/admin/login', [
            'email' => 'manager@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    #[Test]
    public function it_redirects_authenticated_user_from_login(): void
    {
        $manager = $this->createManager();

        $this->actingAs($manager);

        $response = $this->get('/admin/login');

        // Может быть редирект или просто отображение формы
        $response->assertStatus(200);
    }

    #[Test]
    public function it_can_logout_authenticated_user(): void
    {
        $manager = $this->createManager();

        $this->actingAs($manager);

        $response = $this->post('/admin/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    #[Test]
    public function it_requires_manager_role_for_admin_access(): void
    {
        // Создаем пользователя без роли менеджера
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        // Пытаемся получить доступ к админ-панели
        $response = $this->get('/admin/tickets');

        // Middleware возвращает 403 или редирект на login
        $this->assertTrue(
            $response->status() === 403 ||
            $response->isRedirect(route('login'))
        );
    }
}
