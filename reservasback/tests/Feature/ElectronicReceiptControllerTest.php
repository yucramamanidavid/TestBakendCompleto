<?php

namespace Tests\Feature;

use App\Models\ElectronicReceipt;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Entrepreneur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ElectronicReceiptControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_puede_generar_boleta_via_api()
    {
        $emprendedorUser = User::create(['name' => 'Emp', 'email' => 'emprendedor@x.com', 'password' => 'secret']);
        $emprendedor = Entrepreneur::create([
            'user_id'      => $emprendedorUser->id,
            'business_name'=> 'Negocio API',
            'phone'        => '999888777', // obligatorio
            'status'       => 'activo',
        ]);
        $cliente = User::create(['name' => 'Cli', 'email' => 'cliente@x.com', 'password' => 'secret']);

        $producto = Product::create([
            'name' => 'Producto API',
            'entrepreneur_id' => $emprendedor->id,
            'price' => 100.00,
            'stock' => 10,
        ]);

        $reserva = Reservation::create([
            'user_id'         => $cliente->id,
            'product_id'      => $producto->id,
            'reservation_code'=> 'RES-TEST-001',
            'status'          => 'confirmada',
            'total_amount'    => 100,
            'quantity'        => 5,
        ]);

        $response = $this->get("/api/boletas/generar/{$reserva->id}");

        $response->assertStatus(201)
                 ->assertJsonFragment(['message' => 'Boleta generada correctamente.']);

        $this->assertDatabaseHas('electronic_receipts', [
            'reservation_id' => $reserva->id,
            'emprendedor_id' => $emprendedor->id,
            'cliente_id'     => $cliente->id,
        ]);
    }

    /** @test */
    public function test_no_genera_boleta_para_reserva_no_confirmada()
    {
        $emprendedorUser = User::create(['name' => 'Emp', 'email' => 'emp2@x.com', 'password' => 'secret']);
        $emprendedor = Entrepreneur::create([
            'user_id'      => $emprendedorUser->id,
            'business_name'=> 'Negocio 2',
            'phone'        => '999888776', // obligatorio
            'status'       => 'activo',
        ]);
        $cliente = User::create(['name' => 'Cli', 'email' => 'cli2@x.com', 'password' => 'secret']);
        $producto = Product::create([
            'name' => 'Producto2',
            'entrepreneur_id' => $emprendedor->id,
            'price' => 50,
            'stock' => 5,
        ]);

        $reserva = Reservation::create([
            'user_id'         => $cliente->id,
            'product_id'      => $producto->id,
            'reservation_code'=> 'RES-TEST-002',
            'status'          => 'pendiente', // NO confirmada
            'total_amount'    => 500,
            'quantity'        => 2,
        ]);

        $response = $this->get("/api/boletas/generar/{$reserva->id}");

        $response->assertStatus(422)
                 ->assertJsonFragment(['error' => 'La reserva no está confirmada.']);
    }

    /** @test */
    public function test_lista_boletas_de_cliente()
    {
        $emprendedorUser = User::create(['name' => 'Emp', 'email' => 'emp3@x.com', 'password' => 'secret']);
        $emprendedor = Entrepreneur::create([
            'user_id'      => $emprendedorUser->id,
            'business_name'=> 'Negocio 3',
            'phone'        => '999888775',
            'status'       => 'activo',
        ]);
        $cliente = User::create(['name' => 'Cliente', 'email' => 'cli3@x.com', 'password' => 'secret']);
        $producto = Product::create([
            'name' => 'Producto Test',
            'entrepreneur_id' => $emprendedor->id,
            'price' => 99,
            'stock' => 5,
        ]);
        $reserva = Reservation::create([
            'user_id'         => $cliente->id,
            'product_id'      => $producto->id,
            'reservation_code'=> 'RES-TEST-003',
            'status'          => 'confirmada',
            'total_amount'    => 99,
            'quantity'        => 1,
        ]);
        $boleta = ElectronicReceipt::create([
            'reservation_id' => $reserva->id,
            'emprendedor_id' => $emprendedor->id,
            'cliente_id'     => $cliente->id,
            'serie'          => 'B001',
            'numero'         => '000001',
            'monto_total'    => 100,
            'estado_sunat'   => 'pendiente',
        ]);

        $response = $this->get("/api/boletas/cliente/{$cliente->id}");

        $response->assertStatus(200)->assertJsonFragment(['cliente_id' => $cliente->id]);
    }

    /** @test */
    public function test_lista_boletas_de_emprendedor()
    {
        $emprendedorUser = User::create(['name' => 'Emp', 'email' => 'emp4@x.com', 'password' => 'secret']);
        $emprendedor = Entrepreneur::create([
            'user_id'      => $emprendedorUser->id,
            'business_name'=> 'Negocio 4',
            'phone'        => '999888774',
            'status'       => 'activo',
        ]);
        $cliente = User::create(['name' => 'Cliente', 'email' => 'cli4@x.com', 'password' => 'secret']);
        $producto = Product::create([
            'name' => 'Producto Test 2',
            'entrepreneur_id' => $emprendedor->id,
            'price' => 111,
            'stock' => 3,
        ]);
        $reserva = Reservation::create([
            'user_id'         => $cliente->id,
            'product_id'      => $producto->id,
            'reservation_code'=> 'RES-TEST-004',
            'status'          => 'confirmada',
            'total_amount'    => 111,
            'quantity'        => 2,
        ]);
        $boleta = ElectronicReceipt::create([
            'reservation_id' => $reserva->id,
            'emprendedor_id' => $emprendedor->id,
            'cliente_id'     => $cliente->id,
            'serie'          => 'B001',
            'numero'         => '000002',
            'monto_total'    => 111,
            'estado_sunat'   => 'pendiente',
        ]);

        $response = $this->get("/api/boletas/emprendedor/{$emprendedor->id}");

        $response->assertStatus(200)->assertJsonFragment(['emprendedor_id' => $emprendedor->id]);
    }
}
