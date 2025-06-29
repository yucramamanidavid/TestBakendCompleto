<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role; // <-- Importante

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Crear el rol super-admin antes de cada test
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
    }

    /** @test */
    public function puede_listar_contacto_publico()
    {
        Contact::create([
            'address' => 'Avenida Test',
            'phone' => '555-555',
            'email' => 'test@prueba.com',
            'facebook' => 'https://facebook.com/x',
            'instagram' => 'https://instagram.com/y',
            'google_maps_embed' => '<iframe>abc</iframe>'
        ]);

        $response = $this->getJson('/api/contact');
        $response->assertStatus(200)
                 ->assertJsonFragment(['address' => 'Avenida Test']);
    }

    /** @test */
    public function puede_crear_contacto()
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $this->actingAs($admin, 'sanctum');

        $data = [
            'address' => 'Nueva calle',
            'phone' => '888-888',
            'email' => 'nuevo@mail.com',
            'facebook' => 'https://facebook.com/z',
            'instagram' => 'https://instagram.com/z',
            'google_maps_embed' => '<iframe>xyz</iframe>'
        ];

        $response = $this->postJson('/api/contact', $data);
        $response->assertStatus(201)
                 ->assertJsonFragment(['address' => 'Nueva calle']);
        $this->assertDatabaseHas('contacts', ['email' => 'nuevo@mail.com']);
    }

    /** @test */
    public function puede_actualizar_contacto()
    {
        $contact = Contact::create([
            'address' => 'Vieja',
            'phone' => '111',
            'email' => 'v@v.com'
        ]);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $this->actingAs($admin, 'sanctum');

        $data = [
            'address' => 'Actualizada',
            'phone' => '222',
            'email' => 'nuevo@mail.com'
        ];

        $response = $this->putJson("/api/contact/{$contact->id}", $data);
        $response->assertStatus(200)
                 ->assertJsonFragment(['address' => 'Actualizada']);
        $this->assertDatabaseHas('contacts', ['address' => 'Actualizada']);
    }
}
