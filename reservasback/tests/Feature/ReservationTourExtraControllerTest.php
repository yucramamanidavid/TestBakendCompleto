<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\ReservationTourExtra;
use App\Models\TourExtra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationTourExtraControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_add_tour_extra_to_reservation()
    {
        $this->actingAs(User::factory()->create());

        $reservation = Reservation::factory()->create();
        $tourExtra = TourExtra::factory()->create();

        $response = $this->postJson('/api/reservation-tour-extras', [
            'reservation_id' => $reservation->id,
            'tour_extra_id' => $tourExtra->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('reservation_tour_extras', [
            'reservation_id' => $reservation->id,
            'tour_extra_id' => $tourExtra->id,
        ]);
    }

    /** @test */
    public function user_can_delete_tour_extra_from_reservation()
    {
        $this->actingAs(User::factory()->create());

        $extra = ReservationTourExtra::factory()->create();

        $response = $this->deleteJson("/api/reservation-tour-extras/{$extra->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Extra quitado de la reserva']);
        $this->assertDatabaseMissing('reservation_tour_extras', [
            'id' => $extra->id,
        ]);
    }
}
