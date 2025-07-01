<?php

namespace Tests\Unit\Admin;

use App\Http\Controllers\Admin\DashboardStatsController;
use App\Models\User;
use App\Models\Category;
use App\Models\Entrepreneur;
use App\Models\Association;
use App\Models\Reservation;
use App\Models\Place;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStatsControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_counts_users()
    {
        User::factory()->count(2)->create();

        $controller = new DashboardStatsController();
        $response = $controller->countUsers();

        $this->assertEquals(2, $response->getData()->count);
    }

    /** @test */
    public function it_counts_categories()
    {
        Category::factory()->count(5)->create();

        $controller = new DashboardStatsController();
        $response = $controller->countCategories();

        $this->assertEquals(5, $response->getData()->count);
    }

    /** @test */
    public function it_counts_entrepreneurs()
    {
        Entrepreneur::factory()->count(4)->create();

        $controller = new DashboardStatsController();
        $response = $controller->countEntrepreneurs();

        $this->assertEquals(4, $response->getData()->count);
    }

    /** @test */
    public function it_counts_associations()
    {
        Association::factory()->count(3)->create();

        $controller = new DashboardStatsController();
        $response = $controller->countAssociations();

        $this->assertEquals(3, $response->getData()->count);
    }

    /** @test */
    public function it_counts_reservations_excluding_cancelled()
    {
        Reservation::factory()->create(['status' => 'confirmada']);
        Reservation::factory()->create(['status' => 'pendiente']);
        Reservation::factory()->create(['status' => 'cancelada']);

        $controller = new DashboardStatsController();
        $response = $controller->countReservations();

        $this->assertEquals(2, $response->getData()->count);
    }

    /** @test */
    public function it_counts_places()
    {
        Place::factory()->count(6)->create();

        $controller = new DashboardStatsController();
        $response = $controller->countPlaces();

        $this->assertEquals(6, $response->getData()->count);
    }
}
