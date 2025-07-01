<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use App\Models\Reservation;
use App\Models\Product;
use App\Models\Entrepreneur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $entrepreneur;
    protected $entrepreneurUser;
    protected $reservation;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        // Crear los roles si no existen (solo se crea si falta)
        foreach(['super-admin', 'emprendedor', 'cliente'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super-admin');
        $this->entrepreneurUser = User::factory()->create();
        $this->entrepreneurUser->assignRole('emprendedor');
        $this->entrepreneur = Entrepreneur::factory()->create(['user_id' => $this->entrepreneurUser->id]);
        $product = Product::factory()->create(['entrepreneur_id' => $this->entrepreneur->id]);
        $this->reservation = Reservation::factory()->create([
            'product_id' => $product->id,
            'user_id' => $this->admin->id,
        ]);
    }

    /** @test */
    public function admin_can_create_payment_with_image()
    {
        Sanctum::actingAs($this->admin);
        $image = UploadedFile::fake()->image('voucher.jpg');
        $response = $this->postJson('/api/payments', [
            'reservation_id' => $this->reservation->id,
            'payment_method' => 'banco',
            'image_file' => $image,
        ]);
        $response->assertCreated();
        $this->assertDatabaseHas('payments', [
            'reservation_id' => $this->reservation->id,
            'payment_method' => 'banco',
        ]);
        Storage::disk('public')->assertExists('payments/' . $image->hashName());
    }

        /** @test */
    public function entrepreneur_can_confirm_cash_payment()
    {
        Sanctum::actingAs($this->entrepreneurUser);

        $this->reservation->refresh();
        $this->reservation->load('product.entrepreneur');
        $product = $this->reservation->product;

        // DEBUG, puedes comentar después
        $this->assertEquals($this->entrepreneur->id, $product->entrepreneur_id);
        $this->assertEquals($this->entrepreneurUser->id, $this->entrepreneur->user_id);

        $response = $this->postJson('/api/payments', [
            'reservation_id' => $this->reservation->id,
            'payment_method' => 'efectivo',
        ]);
        $response->assertCreated()
            ->assertJsonFragment(['status' => 'confirmado', 'is_confirmed' => true]);
        $this->assertDatabaseHas('payments', ['status' => 'confirmado']);
    }


    /** @test */
    public function cannot_register_cash_payment_if_not_allowed()
    {
        $otherUser = User::factory()->create();
        $otherUser->assignRole('cliente');

        Sanctum::actingAs($otherUser);
        $response = $this->postJson('/api/payments', [
            'reservation_id' => $this->reservation->id,
            'payment_method' => 'efectivo',
        ]);
        $response->assertForbidden();
    }

    /** @test */
    public function admin_can_confirm_payment()
    {
        $payment = Payment::factory()->create([
            'reservation_id' => $this->reservation->id,
            'is_confirmed' => false,
            'status' => 'enviado',
        ]);
        Sanctum::actingAs($this->admin);
        $response = $this->postJson("/api/payments/{$payment->id}/confirm");
        $response->assertOk()
            ->assertJsonFragment(['message' => 'Pago confirmado correctamente.']);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'confirmado',
            'is_confirmed' => true,
        ]);
    }

    /** @test */
    public function only_admin_can_reject_payments()
    {
        $payment = Payment::factory()->create(['reservation_id' => $this->reservation->id, 'status' => 'enviado']);
        $otherUser = User::factory()->create();
        $otherUser->assignRole('cliente');

        Sanctum::actingAs($otherUser);
        $response = $this->postJson("/api/payments/{$payment->id}/reject");
        $response->assertForbidden();
    }

    /** @test */
    public function admin_can_reject_payment_and_cancel_reservation()
    {
        $payment = Payment::factory()->create(['reservation_id' => $this->reservation->id, 'status' => 'enviado']);
        Sanctum::actingAs($this->admin);

        // Debug
        $this->admin->refresh();
        $this->assertTrue($this->admin->hasRole('super-admin'));

        $response = $this->postJson("/api/payments/{$payment->id}/reject");
        $response->assertOk()
            ->assertJsonFragment(['message' => 'Pago rechazado. Reserva cancelada.']);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'rechazado',
        ]);
        $this->assertDatabaseHas('reservations', [
            'id' => $this->reservation->id,
            'status' => 'cancelada',
        ]);
    }


    /** @test */
    public function can_list_payments_for_entrepreneur()
    {
        Sanctum::actingAs($this->entrepreneurUser);
        Payment::factory()->create(['reservation_id' => $this->reservation->id]);
        $response = $this->getJson('/api/payments/for-entrepreneur');
        $response->assertOk()
            ->assertJsonStructure([['id', 'reservation_id', 'status']]);
    }

    /** @test */
    public function non_entrepreneur_cannot_list_payments_for_entrepreneur()
    {
        $user = User::factory()->create();
        $user->assignRole('cliente');

        Sanctum::actingAs($user);
        $response = $this->getJson('/api/payments/for-entrepreneur');
        $response->assertForbidden();
    }
}
