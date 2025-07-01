<?php

namespace Tests\Unit;

use App\Http\Controllers\PackageController;
use App\Models\Package;
use App\Models\Product;
use App\Models\Entrepreneur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PackageControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_returns_all_packages()
    {
        $entrepreneur = Entrepreneur::factory()->create();
        Package::factory()->count(2)->create(['entrepreneur_id' => $entrepreneur->id]);

        $controller = new PackageController();
        $response = $controller->index();

        $this->assertEquals(200, $response->status());
        $this->assertCount(2, $response->getData());
    }

    /** @test */
    public function show_returns_a_package()
    {
        $entrepreneur = Entrepreneur::factory()->create();
        $package = Package::factory()->create(['entrepreneur_id' => $entrepreneur->id]);

        $controller = new PackageController();
        $response = $controller->show($package->id);

        $this->assertEquals($package->id, $response->getData()->id);
    }

    /** @test */
    public function show_returns_404_when_not_found()
    {
        $controller = new PackageController();
        $response = $controller->show(9999);

        $this->assertEquals(404, $response->status());
        $this->assertEquals('Paquete no encontrado', $response->getData()->error);
    }

    /** @test */
    public function destroy_removes_package_and_images()
    {
        $entrepreneur = Entrepreneur::factory()->create();
        $package = Package::factory()->create(['entrepreneur_id' => $entrepreneur->id]);
        // Crear imágenes relacionadas
        $package->images()->create(['image_path' => 'test1.jpg']);
        $package->images()->create(['image_path' => 'test2.jpg']);

        $controller = new PackageController();
        $response = $controller->destroy($package->id);

        $this->assertEquals(200, $response->status());
        $this->assertDatabaseMissing('packages', ['id' => $package->id]);
        $this->assertEquals('Paquete eliminado', $response->getData()->message);
    }

    /** @test */
    public function destroy_returns_404_if_not_found()
    {
        $controller = new PackageController();
        $response = $controller->destroy(9999);

        $this->assertEquals(404, $response->status());
        $this->assertEquals('Paquete no encontrado', $response->getData()->error);
    }

    /** @test */
    public function my_packages_returns_only_user_entrepreneur_packages()
    {
        $user = User::factory()->create();
        $entrepreneur = Entrepreneur::factory()->create(['user_id' => $user->id]);
        Package::factory()->count(3)->create(['entrepreneur_id' => $entrepreneur->id]);

        Auth::shouldReceive('user')->andReturn($user);

        $controller = new PackageController();
        $response = $controller->myPackages();

        $this->assertEquals(200, $response->status());
        $this->assertCount(3, $response->getData());
    }

    /** @test */
    public function my_packages_returns_403_if_not_entrepreneur()
    {
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        $controller = new PackageController();
        $response = $controller->myPackages();

        $this->assertEquals(403, $response->status());
        $this->assertEquals('No eres un emprendedor.', $response->getData()->message);
    }
}
