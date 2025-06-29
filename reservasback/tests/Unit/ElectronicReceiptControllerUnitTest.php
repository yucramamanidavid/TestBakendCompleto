<?php

namespace Tests\Unit;

use App\Http\Controllers\ElectronicReceiptController;
use App\Models\ElectronicReceipt;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Entrepreneur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ElectronicReceiptControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_generar_boleta_para_reserva_confirmada()
    {
        // Crear usuario emprendedor y emprendedor
        $emprendedorUser = User::create(['name' => 'Emp', 'email' => 'emprendedor@x.com', 'password' => 'secret']);
        $emprendedor = Entrepreneur::create([
            'user_id'      => $emprendedorUser->id,
            'business_name'=> 'Negocio Unit',
            'phone'        => '900111222',
            'status'       => 'activo',
        ]);
        // Crear cliente
        $cliente = User::create(['name' => 'Cli', 'email' => 'cliente@x.com', 'password' => 'secret']);

        // Crear producto
        $producto = Product::create([
            'name'           => 'Producto Unit',
            'entrepreneur_id'=> $emprendedor->id,
            'price'          => 200,
            'stock'          => 7,
        ]);

        // Crear reserva confirmada
        $reserva = Reservation::create([
            'user_id'         => $cliente->id,
            'product_id'      => $producto->id,
            'reservation_code'=> 'RES-UNIT-001',
            'status'          => 'confirmada',
            'total_amount'    => 200,
            'quantity'        => 2,
        ]);

        // Llama al controlador
        $controller = new ElectronicReceiptController();
        $response = $controller->generar($reserva->id);

        $this->assertEquals(201, $response->status());
        $this->assertDatabaseHas('electronic_receipts', [
            'reservation_id' => $reserva->id,
            'emprendedor_id' => $emprendedor->id,
            'cliente_id'     => $cliente->id,
        ]);
    }
}
