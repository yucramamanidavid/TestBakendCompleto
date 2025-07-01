<?php

namespace Tests\Feature;

use App\Models\Testimonio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonioControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_lists_all_testimonios()
    {
        // Crea 3 testimonios, uno con user_id y dos sin
        $user = User::factory()->create(['name' => 'Juan Tester']);
        Testimonio::factory()->create(['user_id' => $user->id, 'nombre' => null]);
        Testimonio::factory()->count(2)->create(['nombre' => 'Ana Invitada', 'user_id' => null]);

        $response = $this->getJson('/api/testimonios');

        $response->assertStatus(200)
                 ->assertJsonCount(3)
                 ->assertJsonFragment(['nombre' => 'Juan Tester'])
                 ->assertJsonFragment(['nombre' => 'Ana Invitada']);
    }

    /** @test */
    public function it_creates_a_testimonio_with_nombre()
    {
        $data = [
            'nombre' => 'Pedro Public',
            'estrellas' => 5,
            'comentario' => '¡Excelente!',
        ];

        $response = $this->postJson('/api/testimonios', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                    'nombre' => 'Pedro Public',
                    'estrellas' => 5,
                    'comentario' => '¡Excelente!',
                 ]);

        $this->assertDatabaseHas('testimonios', [
            'nombre' => 'Pedro Public',
            'estrellas' => 5,
        ]);
    }

    /** @test */
    public function it_creates_a_testimonio_with_user_id()
    {
        $user = User::factory()->create(['name' => 'Carlos User']);

        $data = [
            'user_id' => $user->id,
            'estrellas' => 4,
            'comentario' => 'Muy bien',
        ];

        $response = $this->postJson('/api/testimonios', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nombre' => 'Carlos User', 'estrellas' => 4]);
    }

    /** @test */
    public function it_fails_to_create_with_invalid_data()
    {
        $response = $this->postJson('/api/testimonios', [
            'comentario' => '', // faltan estrellas y nombre/user_id
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['estrellas', 'nombre']);
    }
}
