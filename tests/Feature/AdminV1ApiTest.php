<?php

namespace Tests\Feature;

use App\Models\Administrador;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AdminV1ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_v1_login_protects_the_area_and_revokes_its_token(): void
    {
        $admin = $this->createAdmin();

        $this->getJson('/api/v1/admin/recursos')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);

        $token = $this->postJson('/api/v1/admin/login', [
            'nombre_usuario' => 'admin-v1',
            'password' => 'correct-password',
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.administrador.id', $admin->id_administrador)
            ->json('data.token');

        $this->withToken($token)->postJson('/api/v1/admin/logout')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/admin/recursos')->assertUnauthorized();
    }

    public function test_admin_can_manage_products_and_view_soft_deleted_records(): void
    {
        $token = $this->adminToken();

        $id = $this->asAdmin($token, 'POST', '/api/v1/admin/recursos', [
            'nombre' => 'Ibuprofeno',
            'precio' => 45.50,
            'stock' => 12,
            'categoria' => 'Analgésicos',
        ])->assertCreated()
            ->assertJsonPath('success', true)
            ->json('data.id');

        $this->asAdmin($token, 'GET', '/api/v1/admin/recursos')->assertOk()
            ->assertJsonPath('data.0.id', $id)
            ->assertJsonPath('meta.total', 1);

        $this->asAdmin($token, 'GET', "/api/v1/admin/recursos/{$id}")
            ->assertOk()
            ->assertJsonPath('data.nombre', 'Ibuprofeno');

        $this->asAdmin($token, 'PATCH', "/api/v1/admin/recursos/{$id}", ['stock' => 8])
            ->assertOk()
            ->assertJsonPath('data.stock', 8);

        $this->asAdmin($token, 'PUT', "/api/v1/admin/recursos/{$id}", [
            'nombre' => 'Ibuprofeno 400 mg',
            'precio' => 49.90,
            'stock' => 8,
            'categoria' => 'Analgésicos',
        ])->assertOk()->assertJsonPath('data.nombre', 'Ibuprofeno 400 mg');

        $this->asAdmin($token, 'DELETE', "/api/v1/admin/recursos/{$id}")
            ->assertOk();

        $this->asAdmin($token, 'GET', '/api/v1/admin/recursos')->assertOk()
            ->assertJsonPath('data.0.eliminado', true);

        $this->asAdmin($token, 'GET', "/api/v1/admin/recursos/{$id}")
            ->assertOk()
            ->assertJsonPath('data.eliminado', true);

        $this->asAdmin($token, 'GET', '/api/v1/admin/recursos/999')->assertNotFound();
        $this->asAdmin($token, 'POST', '/api/v1/admin/recursos', [])->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['nombre', 'precio', 'stock', 'categoria']]);

        $this->assertNotNull(Producto::withTrashed()->find($id)->deleted_at);
    }

    public function test_admin_levels_and_role_tokens_are_authorized(): void
    {
        $readOnlyToken = $this->adminToken(level: 1, username: 'read-only');

        $this->asAdmin($readOnlyToken, 'GET', '/api/v1/admin/recursos')->assertOk();
        $this->asAdmin($readOnlyToken, 'POST', '/api/v1/admin/recursos', [
            'nombre' => 'Paracetamol',
            'precio' => 15,
            'stock' => 5,
            'categoria' => 'Analgésicos',
        ])->assertForbidden()
            ->assertJsonPath('success', false);

        $user = Usuario::create([
            'nombre' => 'Paciente',
            'apellido' => 'Prueba',
            'email' => 'paciente@example.test',
            'password' => Hash::make('correct-password'),
            'activo' => true,
        ]);
        $userToken = $user->createToken('patient-token', ['paciente'])->plainTextToken;

        $this->asAdmin($userToken, 'GET', '/api/v1/admin/recursos')
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_admin_can_list_users_and_update_account_state_without_role_escalation(): void
    {
        $token = $this->adminToken();
        $user = Usuario::create([
            'nombre' => 'Cliente',
            'apellido' => 'Prueba',
            'email' => 'cliente@example.test',
            'password' => Hash::make('correct-password'),
            'activo' => true,
        ]);
        $userToken = $user->createToken('cliente-token', ['paciente'])->plainTextToken;
        $otherAdmin = $this->createAdmin(['nombre_usuario' => 'admin-secundario', 'nivel' => 1]);
        $otherAdminToken = $otherAdmin->createToken('admin-secundario-token', ['admin'])->plainTextToken;

        $this->asAdmin($token, 'GET', '/api/v1/admin/usuarios')->assertOk()
            ->assertJsonPath('data.usuarios.0.tipo', 'usuario')
            ->assertJsonPath('data.administradores.0.tipo', 'administrador');

        $this->asAdmin($token, 'PATCH', "/api/v1/admin/usuarios/{$user->id_usuario}/estado", [
            'tipo' => 'usuario',
            'activo' => false,
        ])->assertOk()->assertJsonPath('data.activo', false);
        $this->asAdmin($userToken, 'GET', '/api/paciente/perfil')
            ->assertForbidden()
            ->assertJsonPath('message', 'La cuenta está deshabilitada.');

        $this->asAdmin($token, 'PATCH', "/api/v1/admin/usuarios/{$otherAdmin->id_administrador}/estado", [
            'tipo' => 'administrador',
            'activo' => false,
        ])->assertOk()->assertJsonPath('data.activo', false);

        $this->assertDatabaseHas('usuarios', ['id_usuario' => $user->id_usuario, 'activo' => false]);
        $this->assertDatabaseHas('administradores', ['id_administrador' => $otherAdmin->id_administrador, 'activo' => false]);
        $this->asAdmin($otherAdminToken, 'GET', '/api/v1/admin/recursos')
            ->assertForbidden()
            ->assertJsonPath('message', 'La cuenta de administrador está deshabilitada.');
        $this->asAdmin($token, 'PATCH', "/api/v1/admin/usuarios/{$user->id_usuario}/estado", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['tipo', 'activo']);
    }

    private function adminToken(int $level = 3, string $username = 'admin-v1'): string
    {
        $this->createAdmin(['nombre_usuario' => $username, 'nivel' => $level]);

        return $this->postJson('/api/v1/admin/login', [
            'nombre_usuario' => $username,
            'password' => 'correct-password',
        ])->assertOk()->json('data.token');
    }

    private function createAdmin(array $attributes = []): Administrador
    {
        return Administrador::create(array_merge([
            'nombre_completo' => 'Administrador de prueba',
            'nombre_usuario' => 'admin-v1',
            'password' => Hash::make('correct-password'),
            'activo' => true,
            'nivel' => 3,
        ], $attributes));
    }

    private function asAdmin(string $token, string $method, string $uri, array $data = []): TestResponse
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($token)->json($method, $uri, $data);
    }
}
