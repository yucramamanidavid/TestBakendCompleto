<?php

namespace Tests\Unit;

use App\Http\Controllers\TourExtraController;
use App\Models\TourExtra;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class TourExtraControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_show_a_tour_extra()
    {
        // Arrange: crear un TourExtra en la base de datos
        $extra = TourExtra::factory()->create();

        // Act: instanciar el controller y llamar al método
        $controller = new TourExtraController();
        $response = $controller->show($extra->id);

        // Assert: verificar que regresa correctamente
        $this->assertEquals($extra->id, $response->getData()->id);
        $this->assertEquals($extra->name, $response->getData()->name);
    }

    /** @test */
    public function it_can_delete_a_tour_extra()
    {
        $extra = TourExtra::factory()->create();

        $controller = new TourExtraController();
        $response = $controller->destroy($extra->id);

        $this->assertDatabaseMissing('tour_extras', ['id' => $extra->id]);
        $this->assertEquals('Extra eliminado', $response->getData()->message);
    }

    /** @test */
    public function it_can_update_a_tour_extra()
    {
        $extra = TourExtra::factory()->create([
            'name' => 'Original Name',
            'price' => 100,
        ]);

        $request = Request::create('', 'PUT', [
            'name' => 'Updated Name',
            'price' => 200,
        ]);

        $controller = new TourExtraController();
        $response = $controller->update($request, $extra->id);

        $this->assertEquals('Updated Name', $response->getData()->name);
        $this->assertEquals(200, $response->getData()->price);
        $this->assertDatabaseHas('tour_extras', [
            'id' => $extra->id,
            'name' => 'Updated Name',
            'price' => 200,
        ]);
    }
}
