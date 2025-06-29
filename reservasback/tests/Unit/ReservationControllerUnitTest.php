<?php

namespace Tests\Unit;

use App\Http\Controllers\ReservationController;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_returns_all_reservations()
    {
        // Crear 3 reservas en la base de datos de prueba
        Reservation::factory()->count(3)->create();

        $controller = new ReservationController();
        $response = $controller->index();

        $data = $response->getData();
        $this->assertCount(3, $data);
    }

    /** @test */
    /** @test */
    public function show_returns_reservation_with_relations()
    {
        $reservation = Reservation::factory()->create();

        $controller = new ReservationController();
        $response = $controller->show($reservation->id);

        $data = $response->getData();
        $this->assertEquals($reservation->id, $data->id);
        // Verificar que existan las relaciones
        $this->assertTrue(property_exists($data, 'product'));
        $this->assertTrue(property_exists($data, 'user'));
        $this->assertTrue(property_exists($data, 'payment'));
    }

}
