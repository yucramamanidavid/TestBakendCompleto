<?php

namespace Tests\Unit;

use App\Http\Controllers\PaymentController;
use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_returns_payments_filtered_by_status()
    {
        $controller = new PaymentController();

        Payment::factory()->count(2)->create(['status' => 'enviado']);
        Payment::factory()->count(1)->create(['status' => 'confirmado']);

        $request = Request::create('/payments', 'GET', ['status' => 'confirmado']);

        $response = $controller->index($request);
        $data = $response->getData(true);

        $this->assertCount(1, $data);
        $this->assertEquals('confirmado', $data[0]['status']);
    }
}
