<?php

namespace Tests\Unit;

use App\Http\Controllers\HomepageSettingController;
use App\Models\HomepageSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageSettingControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_activa_una_configuracion_y_desactiva_las_anteriores()
    {
        $setting1 = HomepageSetting::create([
            'title_text' => 'Antigua',
            'active' => true,
        ]);
        $setting2 = HomepageSetting::create([
            'title_text' => 'Nueva',
            'active' => false,
        ]);

        $controller = new HomepageSettingController();
        $response = $controller->activate($setting2->id);

        $this->assertEquals(200, $response->status());
        $this->assertDatabaseHas('homepage_settings', [
            'id' => $setting2->id,
            'active' => true,
        ]);
        $this->assertDatabaseHas('homepage_settings', [
            'id' => $setting1->id,
            'active' => false,
        ]);
    }

    /** @test */
    public function test_puede_eliminar_una_configuracion()
    {
        $setting = HomepageSetting::create([
            'title_text' => 'Config Test',
            'active' => false,
            'image_path' => [],
        ]);

        $controller = new HomepageSettingController();
        $response = $controller->destroy($setting->id);

        $this->assertEquals(200, $response->status());
        $this->assertDatabaseMissing('homepage_settings', [
            'id' => $setting->id,
        ]);
    }
}
