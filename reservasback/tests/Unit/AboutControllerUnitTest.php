<?php

namespace Tests\Unit;

use App\Http\Controllers\AboutController;
use App\Models\About;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_devuelve_todos_los_abouts()
    {
        $a1 = About::create(['title' => 'Uno', 'content' => 'X', 'active' => false]);
        $a2 = About::create(['title' => 'Dos', 'content' => 'Y', 'active' => true]);
        $controller = new AboutController();
        $resp = $controller->index();
        $json = $resp->getData();
        $this->assertEquals('Uno', $json[0]->title);
        $this->assertEquals('Dos', $json[1]->title);
    }
}
