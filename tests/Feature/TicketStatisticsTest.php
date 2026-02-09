<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TicketStatisticsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_statistics_for_authenticated_manager(): void
    {
        // Создаем менеджера
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'manager']);
        $user->assignRole($role);

        // Создаем тестовые заявки
        Ticket::factory()->count(2)->create([
            'created_at' => now(),
        ]);

        Ticket::factory()->count(3)->create([
            'created_at' => now()->subDays(2),
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/tickets/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'daily',
                    'weekly',
                    'monthly',
                ],
            ]);

        $data = $response->json('data');
        $this->assertIsInt($data['daily']);
        $this->assertIsInt($data['weekly']);
        $this->assertIsInt($data['monthly']);
    }

    #[Test]
    public function it_denies_statistics_for_unauthenticated_user(): void
    {
        $response = $this->getJson('/api/tickets/statistics');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    #[Test]
    public function it_denies_statistics_for_user_without_manager_role(): void
    {
        $user = User::factory()->create();
        // Не назначаем роль менеджера

        $this->actingAs($user);

        $response = $this->getJson('/api/tickets/statistics');

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Access denied. Manager role required.',
            ]);
    }

    #[Test]
    public function it_calculates_daily_statistics_correctly(): void
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'manager']);
        $user->assignRole($role);

        // Создаем заявки за сегодня
        Ticket::factory()->count(5)->create([
            'created_at' => now(),
        ]);

        // Создаем заявки за вчера
        Ticket::factory()->count(3)->create([
            'created_at' => now()->subDay(),
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/tickets/statistics');

        $data = $response->json('data');
        $this->assertEquals(5, $data['daily']);
    }
}

