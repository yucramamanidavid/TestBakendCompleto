<?php

namespace Tests\Unit;

use App\Http\Controllers\PaymentController;
use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Mockery;

class PaymentControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_all_payments()
    {
        Payment::factory()->count(3)->create();
        $controller = new PaymentController();
        $request = Request::create('/payments', 'GET');
        $response = $controller->index($request);
        $this->assertEquals(200, $response->status());
        $this->assertCount(3, $response->getData());
    }

    /** @test */
    public function it_shows_a_payment()
    {
        $payment = Payment::factory()->create();
        $controller = new PaymentController();
        $response = $controller->show($payment->id);
        $this->assertEquals($payment->id, $response->getData()->id);
    }
}
