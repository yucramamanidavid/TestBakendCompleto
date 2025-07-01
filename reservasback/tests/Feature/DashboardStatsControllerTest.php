<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Category;
use App\Models\Entrepreneur;
use App\Models\Association;
use App\Models\Reservation;
use App\Models\Place;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardStatsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Crea un usuario admin y autentícalo
        $admin = User::factory()->create([
            // personaliza aquí si usas roles o permisos para admin
        ]);
        Sanctum::actingAs($admin, ['*']);
    }

    /** @test */
    public function it_returns_users_count()
    {
        User::factory()->count(5)->create();

        $response = $this->getJson('/api/admin/users/count');

        $response->assertStatus(200)
                 ->assertJson(['count' => 6]); // +1 por el admin
    }

    /** @test */
    public function it_returns_categories_count()
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/admin/categories/count');
        $response->assertStatus(200)
                 ->assertJson(['count' => 3]);
    }

    /** @test */
    public function it_returns_entrepreneurs_count()
    {
        Entrepreneur::factory()->count(4)->create();

        $response = $this->getJson('/api/admin/entrepreneurs/count');
        $response->assertStatus(200)
                 ->assertJson(['count' => 4]);
    }

    /** @test */
    public function it_returns_associations_count()
    {
        Association::factory()->count(2)->create();

        $response = $this->getJson('/api/admin/associations/count');
        $response->assertStatus(200)
                 ->assertJson(['count' => 2]);
    }

    /** @test */
    public function it_returns_reservations_count_excluding_cancelled()
    {
        Reservation::factory()->count(3)->create(['status' => 'confirmada']);
        Reservation::factory()->count(2)->create(['status' => 'cancelada']);

        $response = $this->getJson('/api/admin/reservations/count');
        $response->assertStatus(200)
                 ->assertJson(['count' => 3]);
    }

    /** @test */
    public function it_returns_places_count()
    {
        Place::factory()->count(7)->create();

        $response = $this->getJson('/api/admin/places/count');
        $response->assertStatus(200)
                 ->assertJson(['count' => 7]);
    }
}
