<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_authenticated_user_can_create_payment()
    {
        Storage::fake('public');

        // Crear un usuario emprendedor
        $user = \App\Models\User::factory()->create(['role' => 'emprendedor']);
        $entrepreneur = \App\Models\Entrepreneur::factory()->create(['user_id' => $user->id]);

        // Producto que pertenece a ese emprendedor
        $product = \App\Models\Product::factory()->create([
            'entrepreneur_id' => $entrepreneur->id,
        ]);

        // Crear reserva asociada al producto
        $reservation = \App\Models\Reservation::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 2,
        ]);

        // Archivo simulado
        $file = UploadedFile::fake()->image('comprobante.jpg');

        $this->actingAs($user, 'sanctum'); // Usa sanctum si tu API lo usa

        // Enviar solicitud
        $response = $this->postJson('/api/payments', [
            'reservation_id' => $reservation->id,
            'payment_method' => 'efectivo',
            'image_file'     => $file,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('payments', [
            'reservation_id' => $reservation->id,
            'payment_method' => 'efectivo',
        ]);
    }


    /** @test */
    public function only_superadmin_can_delete_payment()
    {
        $admin = User::factory()->create(['role' => 'super-admin']);
        $this->actingAs($admin);

        $payment = Payment::factory()->create();

        $response = $this->deleteJson("/api/payments/{$payment->id}");
        $response->assertStatus(200);
        $this->assertSoftDeleted('payments', ['id' => $payment->id]);
    }
}
