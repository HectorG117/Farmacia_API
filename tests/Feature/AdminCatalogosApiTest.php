<?php

namespace Tests\Feature;

use App\Models\Administrador;
use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Controladores del administrador que antes regresaban vistas: ahora solo JSON
 * con el código HTTP correspondiente (200, 201, 404, 409, 422, 403).
 */
class AdminCatalogosApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_ninguna_ruta_regresa_html(): void
    {
        $this->get('/')->assertOk()->assertHeader('Content-Type', 'application/json');
        $this->get('/ruta-que-no-existe')->assertNotFound()->assertHeader('Content-Type', 'application/json');
        $this->get('/api/productos/999')->assertNotFound()->assertJsonStructure(['mensaje']);
    }

    public function test_crud_de_especialidades_con_sus_codigos(): void
    {
        $token = $this->tokenAdmin(nivel: 3);

        $id = $this->conToken($token, 'POST', '/api/especialidades', ['nombre' => 'Pediatría'])
            ->assertCreated()->json('data.id_especialidad');

        $this->conToken($token, 'POST', '/api/especialidades', [])
            ->assertUnprocessable()->assertJsonValidationErrors('nombre');

        $this->conToken($token, 'GET', "/api/especialidades/{$id}")->assertOk()->assertJsonPath('data.nombre', 'Pediatría');
        $this->conToken($token, 'PUT', "/api/especialidades/{$id}", ['nombre' => 'Pediatría general'])
            ->assertOk()->assertJsonPath('data.nombre', 'Pediatría general');
        $this->conToken($token, 'GET', '/api/especialidades/999')->assertNotFound();

        // En uso por un médico -> 409
        Medico::create([
            'id_usuario' => $this->crearUsuario('medico@example.test')->id_usuario,
            'cedula' => 'CED-1', 'especialidad_id' => $id, 'consultorio' => '1',
        ]);
        $this->conToken($token, 'DELETE', "/api/especialidades/{$id}")->assertStatus(409);

        $libre = Especialidad::create(['nombre' => 'Dermatología']);
        $this->conToken($token, 'DELETE', "/api/especialidades/{$libre->id_especialidad}")->assertOk();
        $this->assertDatabaseMissing('especialidades', ['id_especialidad' => $libre->id_especialidad]);
    }

    public function test_productos_papelera_restaurar_y_borrado_definitivo(): void
    {
        $token = $this->tokenAdmin(nivel: 3);

        $id = $this->conToken($token, 'POST', '/api/productos', [
            'nombre' => 'Ibuprofeno', 'precio' => 45.5, 'stock' => 10, 'categoria' => 'Analgésicos',
            'imagen' => UploadedFile::fake()->image('ibuprofeno.png'),
        ])->assertCreated()->json('data.id');

        $this->assertStringStartsWith('uploads/productos/', Producto::find($id)->imagen);

        $this->conToken($token, 'DELETE', "/api/productos/{$id}")->assertOk();
        $this->getJson("/api/productos/{$id}")->assertNotFound();
        $this->conToken($token, 'GET', '/api/productos/papelera')->assertOk()->assertJsonCount(1, 'data');

        $this->conToken($token, 'PUT', "/api/productos/{$id}/restaurar")->assertOk();
        $this->getJson("/api/productos/{$id}")->assertOk();

        // Solo se borra de forma definitiva lo que está en la papelera
        $this->conToken($token, 'DELETE', "/api/productos/{$id}/definitivo")->assertNotFound();
        $this->conToken($token, 'DELETE', "/api/productos/{$id}")->assertOk();
        $this->conToken($token, 'DELETE', "/api/productos/{$id}/definitivo")->assertOk();
        $this->assertNull(Producto::withTrashed()->find($id));
    }

    public function test_los_niveles_del_administrador_se_respetan(): void
    {
        $lectura = $this->tokenAdmin(nivel: 1, usuario: 'lectura');
        $editor = $this->tokenAdmin(nivel: 2, usuario: 'editor');

        $this->conToken($lectura, 'GET', '/api/especialidades')->assertOk();
        $this->conToken($lectura, 'POST', '/api/especialidades', ['nombre' => 'X'])->assertForbidden();

        $this->conToken($editor, 'POST', '/api/especialidades', ['nombre' => 'Cardiología'])->assertCreated();
        $this->conToken($editor, 'GET', '/api/roles')->assertForbidden(); // roles: solo nivel 3

        $rol = Rol::create(['nombre' => 'Cliente']);
        $this->conToken($editor, 'DELETE', "/api/roles/{$rol->id_rol}")->assertForbidden();
    }

    public function test_modulos_genericos_usuarios_y_asignacion_de_roles(): void
    {
        $token = $this->tokenAdmin(nivel: 3);

        $idUsuario = $this->conToken($token, 'POST', '/api/usuarios', [
            'nombre' => 'Luis', 'apellido' => 'Pérez', 'email' => 'luis@example.test',
            'password' => 'secreto123', 'activo' => true,
        ])->assertCreated()->assertJsonMissingPath('data.password')->json('data.id_usuario');

        $this->assertTrue(Hash::check('secreto123', Usuario::find($idUsuario)->password));
        $this->conToken($token, 'GET', '/api/usuarios')->assertOk()->assertJsonPath('total', 1);
        $this->conToken($token, 'GET', '/api/usuarios/999')->assertNotFound();

        $rol = $this->conToken($token, 'POST', '/api/roles', ['nombre' => 'Cliente'])->assertCreated()->json('data.id_rol');

        $this->conToken($token, 'POST', '/api/usuario-roles', ['id_usuario' => $idUsuario, 'id_rol' => $rol])->assertCreated();
        $this->conToken($token, 'POST', '/api/usuario-roles', ['id_usuario' => $idUsuario, 'id_rol' => $rol])->assertOk();
        $this->conToken($token, 'DELETE', "/api/usuario-roles/{$idUsuario}/{$rol}")->assertOk();
        $this->conToken($token, 'DELETE', "/api/usuario-roles/{$idUsuario}/{$rol}")->assertNotFound();

        $this->conToken($token, 'POST', '/api/logs', ['accion' => 'Prueba', 'modulo' => 'Tests'])
            ->assertUnprocessable()->assertJsonValidationErrors('id_usuario');
        $this->conToken($token, 'POST', '/api/logs', ['id_usuario' => $idUsuario, 'accion' => 'Prueba', 'modulo' => 'Tests'])->assertCreated();
        $this->conToken($token, 'GET', '/api/logs')->assertOk()->assertJsonCount(1, 'data');
    }

    // ------------------------------------------------------------------

    private function conToken(string $token, string $metodo, string $uri, array $datos = []): TestResponse
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($token)->json($metodo, $uri, $datos);
    }

    private function tokenAdmin(int $nivel, string $usuario = 'admin'): string
    {
        Administrador::create([
            'nombre_completo' => 'Admin', 'nombre_usuario' => $usuario,
            'password' => 'secreto123', 'activo' => true, 'nivel' => $nivel,
        ]);

        return $this->postJson('/api/admin/login', ['nombre_usuario' => $usuario, 'password' => 'secreto123'])->json('token');
    }

    private function crearUsuario(string $email): Usuario
    {
        return Usuario::create([
            'nombre' => 'Usuario', 'apellido' => 'Prueba', 'email' => $email,
            'password' => Hash::make('secreto123'), 'activo' => true,
        ]);
    }
}
