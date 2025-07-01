<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\TestimonioController;
use App\Models\Testimonio;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonioControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_stores_testimonio_with_nombre()
    {
        $request = Request::create('/api/testimonios', 'POST', [
            'nombre' => 'Test User',
            'estrellas' => 5,
            'comentario' => 'Muy bueno',
        ]);

        $controller = new TestimonioController();
        $response = $controller->store($request);

        $this->assertDatabaseHas('testimonios', ['nombre' => 'Test User']);
        $this->assertEquals('Test User', $response->getData()->nombre);
    }
}
