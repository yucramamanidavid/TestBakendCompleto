<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Product;
use App\Models\Entrepreneur;
use App\Models\User;
use App\Models\PackageImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PackageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $entrepreneur;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->user = User::factory()->create();
        $this->entrepreneur = Entrepreneur::factory()->create(['user_id' => $this->user->id]);
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_list_all_packages()
    {
        Package::factory()->count(2)->create(['entrepreneur_id' => $this->entrepreneur->id]);

        $response = $this->getJson('/api/packages');

        $response->assertStatus(200)
                 ->assertJsonCount(2);
    }

    /** @test */
    public function it_can_show_a_package()
    {
        $package = Package::factory()->create(['entrepreneur_id' => $this->entrepreneur->id]);

        $response = $this->getJson("/api/packages/{$package->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $package->id]);
    }

    /** @test */
    public function it_returns_404_if_package_not_found()
    {
        $response = $this->getJson("/api/packages/999999");
        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_create_a_package_with_images()
    {
        $products = Product::factory()->count(2)->create(['entrepreneur_id' => $this->entrepreneur->id]);

        $data = [
            'name' => 'Super Paquete',
            'description' => 'Incluye todo',
            'product_ids' => $products->pluck('id')->toArray(),
            'images' => [
                UploadedFile::fake()->image('photo1.jpg'),
                UploadedFile::fake()->image('photo2.jpg'),
            ]
        ];

        $response = $this->postJson('/api/packages', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['message' => 'Paquete creado correctamente']);

        $this->assertDatabaseHas('packages', ['name' => 'Super Paquete']);
        $this->assertDatabaseCount('package_images', 2);

        // Verifica que los archivos se hayan almacenado
        $images = PackageImage::all();
        foreach ($images as $img) {
            Storage::disk('public')->assertExists($img->image_path);
        }
    }

    /** @test */
    public function it_can_update_a_package()
    {
        $package = Package::factory()->create(['entrepreneur_id' => $this->entrepreneur->id]);
        $newProducts = Product::factory()->count(2)->create(['entrepreneur_id' => $this->entrepreneur->id]);
        $data = [
            'name' => 'Paquete Actualizado',
            'product_ids' => $newProducts->pluck('id')->toArray(),
            'images' => [
                UploadedFile::fake()->image('new1.jpg'),
            ]
        ];

        $response = $this->putJson("/api/packages/{$package->id}", $data);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Paquete actualizado']);

        $this->assertDatabaseHas('packages', ['name' => 'Paquete Actualizado']);
    }

    /** @test */
    /** @test */
    public function it_can_delete_a_package_and_its_images()
    {
        $package = Package::factory()->create(['entrepreneur_id' => $this->entrepreneur->id]);
        $image = PackageImage::factory()->create([
            'package_id' => $package->id,
            'image_path' => 'package_images/test.jpg'
        ]);
        Storage::disk('public')->put($image->image_path, 'fake-image-content');

        // Verifica que el archivo realmente existe antes de borrar
        $this->assertTrue(Storage::disk('public')->exists($image->image_path));

        $response = $this->deleteJson("/api/packages/{$package->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Paquete eliminado']);
        $this->assertDatabaseMissing('packages', ['id' => $package->id]);
        $this->assertDatabaseMissing('package_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($image->image_path);
    }


    /** @test */
    public function it_can_list_authenticated_entrepreneur_packages()
    {
        $package = Package::factory()->create(['entrepreneur_id' => $this->entrepreneur->id]);

        $response = $this->getJson('/api/packages/my');

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $package->id]);
    }

    /** @test */
    public function it_returns_403_if_user_is_not_entrepreneur_on_my_packages()
    {
        // Crea usuario sin entrepreneur
        $otherUser = User::factory()->create();
        Sanctum::actingAs($otherUser);

        $response = $this->getJson('/api/packages/my');

        $response->assertStatus(403)
                 ->assertJson(['message' => 'No eres un emprendedor.']);
    }

    // Puedes agregar tests para entrepreneurReservations, pero normalmente requieren más factories y relaciones.
}
