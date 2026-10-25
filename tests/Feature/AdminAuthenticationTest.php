<?php

namespace Tests\Feature;

use App\Models\Administrador;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_the_admin_area_to_login(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Nombre de usuario');
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get('/productos/crear')->assertRedirect(route('login'));
        $this->get('/productos')->assertOk();
    }

    public function test_active_administrator_can_sign_in_and_access_the_admin_area(): void
    {
        $administrador = $this->createAdministrator();
        $this->assertNotSame('correct-password', $administrador->password);
        $this->assertTrue(Hash::check('correct-password', $administrador->password));

        $this->post(route('login.store'), [
            'nombre_usuario' => 'admin',
            'password' => 'correct-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($administrador, 'admin');
        $this->assertGuest('web');
        $this->get('/admin')
            ->assertOk()
            ->assertSee('Inicio de sesión correcto.');
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->createAdministrator();

        $this->from(route('login'))
            ->post(route('login.store'), [
                'nombre_usuario' => 'admin',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'El nombre de usuario o la contraseña son incorrectos.');

        $this->assertGuest('admin');
        $this->get(route('login'))->assertSee('El nombre de usuario o la contraseña son incorrectos.');
    }

    public function test_inactive_administrator_cannot_sign_in_even_with_the_correct_password(): void
    {
        $this->createAdministrator(['activo' => false]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'nombre_usuario' => 'admin',
                'password' => 'correct-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'La cuenta de administrador está deshabilitada.');

        $this->assertGuest('admin');
        $this->get(route('login'))->assertSee('La cuenta de administrador está deshabilitada.');
    }

    public function test_signing_out_invalidates_admin_access(): void
    {
        $administrador = $this->createAdministrator();
        $this->actingAs($administrador, 'admin');

        $this->post(route('admin.logout'))->assertRedirect(route('login'));

        $this->assertGuest('admin');
        $this->get(route('login'))->assertSee('La sesión se cerró correctamente.');
        $this->get('/admin')->assertRedirect(route('login'));
    }

    private function createAdministrator(array $attributes = []): Administrador
    {
        return Administrador::create(array_merge([
            'nombre_completo' => 'Administrador de prueba',
            'nombre_usuario' => 'admin',
            'password' => Hash::make('correct-password'),
            'activo' => true,
        ], $attributes));
    }
}
