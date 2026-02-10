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

    #[Test]
    public function it_calculates_weekly_statistics_correctly(): void
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'manager']);
        $user->assignRole($role);

        // Создаем заявки в текущей неделе
        Ticket::factory()->count(7)->create([
            'created_at' => now()->startOfWeek()->addDays(2),
        ]);

        Ticket::factory()->count(3)->create([
            'created_at' => now()->endOfWeek()->subDays(1),
        ]);

        // Создаем заявки вне текущей недели
        Ticket::factory()->count(2)->create([
            'created_at' => now()->startOfWeek()->subDay(),
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/tickets/statistics');

        $data = $response->json('data');
        $this->assertEquals(10, $data['weekly']);
    }

    #[Test]
    public function it_calculates_monthly_statistics_correctly(): void
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'manager']);
        $user->assignRole($role);

        // Создаем заявки в текущем месяце
        Ticket::factory()->count(12)->create([
            'created_at' => now()->startOfMonth()->addDays(5),
        ]);

        Ticket::factory()->count(8)->create([
            'created_at' => now()->endOfMonth()->subDays(3),
        ]);

        // Создаем заявки вне текущего месяца
        Ticket::factory()->count(5)->create([
            'created_at' => now()->startOfMonth()->subDay(),
        ]);

        Ticket::factory()->count(3)->create([
            'created_at' => now()->endOfMonth()->addDay(),
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/tickets/statistics');

        $data = $response->json('data');
        $this->assertEquals(20, $data['monthly']);
    }

    #[Test]
    public function it_excludes_tickets_outside_period(): void
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'manager']);
        $user->assignRole($role);

        // Создаем заявки за сегодня
        Ticket::factory()->count(2)->create([
            'created_at' => now(),
        ]);

        // Создаем заявки в текущей неделе, но не сегодня (вчера, если сегодня не начало недели)
        $yesterday = now()->subDay();
        if ($yesterday->isSameWeek(now())) {
            Ticket::factory()->count(3)->create([
                'created_at' => $yesterday,
            ]);
        } else {
            // Если вчера была другая неделя, создаем в начале текущей недели
            Ticket::factory()->count(3)->create([
                'created_at' => now()->startOfWeek()->addDays(1),
            ]);
        }

        // Создаем заявки в текущем месяце, но не в текущей неделе
        $oldDate = now()->startOfMonth();
        if ($oldDate->isSameWeek(now())) {
            $oldDate = $oldDate->addWeeks(1);
        }
        Ticket::factory()->count(4)->create([
            'created_at' => $oldDate,
        ]);

        // Создаем заявки вне всех периодов
        Ticket::factory()->count(10)->create([
            'created_at' => now()->subMonths(2),
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/tickets/statistics');

        $data = $response->json('data');
        $this->assertEquals(2, $data['daily']); // Только за сегодня
        $this->assertEquals(5, $data['weekly']); // За сегодня + в текущей неделе (но не сегодня)
        $this->assertEquals(9, $data['monthly']); // Все в текущем месяце
    }
}

