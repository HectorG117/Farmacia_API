<?php

namespace Tests\Feature;

use App\Models\Administrador;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Autenticación del administrador en la API (token de Sanctum, respuestas JSON).
 */
class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_receives_401_in_json_from_the_admin_area(): void
    {
        // Sin "Accept: application/json" también debe responder JSON, nunca una vista ni una redirección
        $this->get('/api/admin/dashboard')
            ->assertUnauthorized()
            ->assertHeader('Content-Type', 'application/json');

        $this->post('/api/productos')->assertUnauthorized();
        $this->getJson('/api/productos')->assertOk()->assertJsonStructure(['data']);
    }

    public function test_active_administrator_can_sign_in_and_access_the_admin_area(): void
    {
        $administrador = $this->createAdministrator();
        $this->assertTrue(Hash::check('correct-password', $administrador->password));

        $token = $this->postJson('/api/admin/login', [
            'nombre_usuario' => 'admin',
            'password'       => 'correct-password',
        ])->assertOk()
            ->assertJsonPath('mensaje', 'Inicio de sesión correcto.')
            ->assertJsonPath('administrador.id_administrador', $administrador->id_administrador)
            ->json('token');

        $this->withToken($token)->getJson('/api/admin/dashboard')
            ->assertOk()
            ->assertJsonStructure(['data' => ['estadisticas', 'proximas_citas', 'ultimas_recetas', 'stock_bajo']]);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->createAdministrator();

        $this->postJson('/api/admin/login', [
            'nombre_usuario' => 'admin',
            'password'       => 'wrong-password',
        ])->assertUnauthorized()
            ->assertJsonPath('mensaje', 'El nombre de usuario o la contraseña son incorrectos.');

        $this->postJson('/api/admin/login', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre_usuario', 'password']);
    }

    public function test_inactive_administrator_cannot_sign_in_even_with_the_correct_password(): void
    {
        $this->createAdministrator(['activo' => false]);

        $this->postJson('/api/admin/login', [
            'nombre_usuario' => 'admin',
            'password'       => 'correct-password',
        ])->assertForbidden()
            ->assertJsonPath('mensaje', 'La cuenta de administrador está deshabilitada.');
    }

    public function test_signing_out_invalidates_admin_access(): void
    {
        $this->createAdministrator();

        $token = $this->postJson('/api/admin/login', [
            'nombre_usuario' => 'admin',
            'password'       => 'correct-password',
        ])->json('token');

        $this->withToken($token)->postJson('/api/admin/logout')
            ->assertOk()
            ->assertJsonPath('mensaje', 'La sesión se cerró correctamente.');

        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/admin/dashboard')->assertUnauthorized();
    }

    private function createAdministrator(array $attributes = []): Administrador
    {
        return Administrador::create(array_merge([
            'nombre_completo' => 'Administrador de prueba',
            'nombre_usuario'  => 'admin',
            'password'        => Hash::make('correct-password'),
            'activo'          => true,
            'nivel'           => 3,
        ], $attributes));
    }
}
