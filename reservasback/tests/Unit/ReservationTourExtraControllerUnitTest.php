<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Reservation;
use App\Models\TourExtra;
use App\Models\ReservationTourExtra;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReservationTourExtraControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function puede_crear_reservation_tour_extra()
    {
        $reservation = Reservation::factory()->create();
        $tourExtra = TourExtra::factory()->create();

        $extra = ReservationTourExtra::create([
            'reservation_id' => $reservation->id,
            'tour_extra_id' => $tourExtra->id
        ]);

        $this->assertDatabaseHas('reservation_tour_extras', [
            'reservation_id' => $reservation->id,
            'tour_extra_id' => $tourExtra->id
        ]);
    }

    /** @test */
    public function relaciona_con_reservation()
    {
        $reservation = Reservation::factory()->create();
        $tourExtra = TourExtra::factory()->create();

        $extra = ReservationTourExtra::create([
            'reservation_id' => $reservation->id,
            'tour_extra_id' => $tourExtra->id
        ]);
        $extra->refresh();

        $this->assertInstanceOf(Reservation::class, $extra->reservation);
    }

    /** @test */
    public function relaciona_con_tour_extra()
    {
        $reservation = Reservation::factory()->create();
        $tourExtra = TourExtra::factory()->create();

        $extra = ReservationTourExtra::create([
            'reservation_id' => $reservation->id,
            'tour_extra_id' => $tourExtra->id
        ]);

        $this->assertInstanceOf(TourExtra::class, $extra->tourExtra);
    }
}
