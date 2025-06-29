<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_create_reservation_with_sufficient_stock()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'price' => 100]);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/reservations', [
            'product_id' => $product->id,
            'quantity' => 2,
            'reservation_date' => now()->toDateString(),
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('reservations', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 8, // Stock reduced by 2
        ]);
    }

    /** @test */
    public function cannot_create_reservation_if_stock_insufficient()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 1]);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/reservations', [
            'product_id' => $product->id,
            'quantity' => 5,
            'reservation_date' => now()->toDateString(),
        ]);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Stock insuficiente']);
    }

    /** @test */
    public function user_can_update_reservation()
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum');

        $response = $this->putJson("/api/reservations/{$reservation->id}", [
            'product_id' => $reservation->product_id,
            'package_id' => null,
            'quantity' => 3,
            'reservation_date' => now()->addDay()->toDateString(),
            'status' => 'confirmada',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'quantity' => 3,
            'status' => 'confirmada',
        ]);
    }

    /** @test */
    public function user_can_delete_own_reservation()
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum');

        $response = $this->deleteJson("/api/reservations/{$reservation->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('reservations', ['id' => $reservation->id]);
    }

    /** @test */
    public function user_cannot_delete_others_reservation()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $reservation = Reservation::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($user, 'sanctum');

        $response = $this->deleteJson("/api/reservations/{$reservation->id}");

        $response->assertStatus(403);
    }
}
