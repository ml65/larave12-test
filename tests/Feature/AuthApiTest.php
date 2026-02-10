<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_login_with_valid_credentials(): void
    {
        // Создаем менеджера
        $role = Role::firstOrCreate(['name' => 'manager']);
        $user = User::factory()->create([
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole($role);

        $response = $this->postJson('/api/login', [
            'email' => 'manager@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ]);

        $data = $response->json();
        $this->assertNotEmpty($data['token']);
        $this->assertEquals($user->id, $data['user']['id']);
        $this->assertEquals($user->email, $data['user']['email']);
    }

    #[Test]
    public function it_denies_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'manager@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials.',
            ]);
    }

    #[Test]
    public function it_denies_login_for_user_without_manager_role(): void
    {
        // Создаем пользователя без роли менеджера
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Access denied. Manager role required.',
            ]);
    }

    #[Test]
    public function it_can_logout_authenticated_user(): void
    {
        $role = Role::firstOrCreate(['name' => 'manager']);
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);
        $user->assignRole($role);

        // Создаем токен напрямую
        $token = $user->createToken('api-token')->plainTextToken;

        // Выходим с использованием токена
        $response = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out successfully.',
            ]);
    }

    #[Test]
    public function it_denies_logout_for_unauthenticated_user(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}

