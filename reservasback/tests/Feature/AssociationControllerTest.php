<?php

namespace Tests\Feature;

use App\Models\Association;
use App\Models\Entrepreneur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssociationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear usuario autenticado
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_list_all_associations()
    {
        Association::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/associations');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    /** @test */
    public function it_can_create_an_association()
    {
        $data = [
            'name' => 'Asociación Integración',
            'description' => 'Descripción de prueba',
            'region' => 'Puno',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/associations', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'name' => 'Asociación Integración',
                     'description' => 'Descripción de prueba',
                     'region' => 'Puno',
                 ]);

        $this->assertDatabaseHas('associations', [
            'name' => 'Asociación Integración',
        ]);
    }

    /** @test */
    public function it_can_show_a_single_association_with_entrepreneurs()
    {
        $association = Association::factory()->create();
        Entrepreneur::factory()->count(2)->create(['association_id' => $association->id]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson("/api/associations/{$association->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'id' => $association->id,
                     'name' => $association->name,
                 ])
                 ->assertJsonStructure([
                     'id',
                     'name',
                     'description',
                     'region',
                     'entrepreneurs',
                 ]);
    }

    /** @test */
    public function it_can_update_an_association()
    {
        $association = Association::factory()->create([
            'name' => 'Original',
        ]);

        $data = [
            'name' => 'Actualizada',
            'region' => 'Lima',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson("/api/associations/{$association->id}", $data);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'name' => 'Actualizada',
                     'region' => 'Lima',
                 ]);

        $this->assertDatabaseHas('associations', [
            'id' => $association->id,
            'name' => 'Actualizada',
        ]);
    }

    /** @test */
    public function it_can_delete_an_association_and_its_entrepreneurs()
    {
        $association = Association::factory()->create();
        Entrepreneur::factory()->count(2)->create(['association_id' => $association->id]);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson("/api/associations/{$association->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Asociación y emprendedores eliminados']);

        $this->assertDatabaseMissing('associations', [
            'id' => $association->id,
        ]);

        $this->assertDatabaseMissing('entrepreneurs', [
            'association_id' => $association->id,
        ]);
    }

    /** @test */
    public function it_can_count_all_associations()
    {
        Association::factory()->count(4)->create();

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/associations/count');

        $response->assertStatus(200)
                 ->assertExactJson(['count' => 4]);
    }
}
