<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\TourExtra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourExtraControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_list_all_extras_for_a_tour()
    {
        $tour = Tour::factory()->create();
        TourExtra::factory()->count(3)->create(['tour_id' => $tour->id]);

        $response = $this->getJson("/api/tours/{$tour->id}/extras");

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    /** @test */
    public function it_can_create_a_tour_extra()
    {
        $tour = Tour::factory()->create();

        $data = [
            'name' => 'Extra Almuerzo',
            'description' => 'Incluye menú completo',
            'price' => 45.50
        ];

        $response = $this->postJson("/api/tours/{$tour->id}/extras", $data);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'name' => 'Extra Almuerzo',
                     'description' => 'Incluye menú completo',
                     'price' => 45.50
                 ]);

        $this->assertDatabaseHas('tour_extras', [
            'tour_id' => $tour->id,
            'name' => 'Extra Almuerzo',
        ]);
    }

    /** @test */
    public function it_can_show_a_tour_extra()
    {
        $extra = TourExtra::factory()->create();

        $response = $this->getJson("/api/tour-extras/{$extra->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'id' => $extra->id,
                     'name' => $extra->name
                 ]);
    }

    /** @test */
    public function it_can_update_a_tour_extra()
    {
        $extra = TourExtra::factory()->create([
            'name' => 'Original Extra'
        ]);

        $data = [
            'name' => 'Updated Extra',
            'price' => 99.99
        ];

        $response = $this->putJson("/api/tour-extras/{$extra->id}", $data);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'name' => 'Updated Extra',
                     'price' => 99.99
                 ]);

        $this->assertDatabaseHas('tour_extras', [
            'id' => $extra->id,
            'name' => 'Updated Extra',
        ]);
    }

    /** @test */
    public function it_can_delete_a_tour_extra()
    {
        $extra = TourExtra::factory()->create();

        $response = $this->deleteJson("/api/tour-extras/{$extra->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Extra eliminado']);

        $this->assertDatabaseMissing('tour_extras', [
            'id' => $extra->id,
        ]);
    }
}
