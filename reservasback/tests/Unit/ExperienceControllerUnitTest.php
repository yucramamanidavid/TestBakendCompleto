<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\ExperienceController;
use App\Models\Experience;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExperienceControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_devuelve_todas_las_experiencias_ordenadas()
    {
        $exp1 = Experience::create([
            'title' => 'Exp1', 'slug' => 'exp1', 'category' => 'cat', 'content' => 'x', 'order' => 2
        ]);
        $exp2 = Experience::create([
            'title' => 'Exp2', 'slug' => 'exp2', 'category' => 'cat', 'content' => 'y', 'order' => 1
        ]);
        $controller = new ExperienceController();
        $result = $controller->index();
        $this->assertEquals('Exp2', $result[0]->title);
        $this->assertEquals('Exp1', $result[1]->title);
    }
}
