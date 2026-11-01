<?php

namespace Tests\Feature;

use App\Models\Administrador;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Producto;
use App\Models\Receta;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Pruebas de la API por rol: login de cada rol, respuestas JSON y que el token
 * de un rol no pueda usar las rutas de otro.
 */
class RolesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_cada_rol_inicia_sesion_y_recibe_un_token(): void
    {
        $this->crearAdmin();
        $medico = $this->crearMedico('medico@example.test', 'CED-1');
        $paciente = $this->crearPaciente('paciente@example.test');

        $this->postJson('/api/admin/login', ['nombre_usuario' => 'admin', 'password' => 'secreto123'])
            ->assertOk()->assertJsonPath('rol', 'admin')->assertJsonStructure(['token']);

        $this->postJson('/api/medico/login', ['email' => $medico->usuario->email, 'password' => 'secreto123'])
            ->assertOk()->assertJsonPath('rol', 'medico')->assertJsonPath('medico.id_medico', $medico->id_medico);

        $this->postJson('/api/paciente/login', ['email' => $paciente->email, 'password' => 'secreto123'])
            ->assertOk()->assertJsonPath('rol', 'paciente');

        // Un médico no puede entrar como paciente
        $this->postJson('/api/paciente/login', ['email' => $medico->usuario->email, 'password' => 'secreto123'])
            ->assertForbidden();

        // Un paciente no puede entrar como médico
        $this->postJson('/api/medico/login', ['email' => $paciente->email, 'password' => 'secreto123'])
            ->assertForbidden();

        $this->postJson('/api/medico/login', ['email' => $paciente->email, 'password' => 'incorrecta'])
            ->assertUnauthorized();
    }

    public function test_el_token_de_un_rol_no_sirve_en_las_rutas_de_otro(): void
    {
        $this->crearAdmin();
        $medico = $this->crearMedico('medico@example.test', 'CED-1');
        $paciente = $this->crearPaciente('paciente@example.test');

        $tokenAdmin = $this->postJson('/api/admin/login', ['nombre_usuario' => 'admin', 'password' => 'secreto123'])->json('token');
        $tokenMedico = $this->postJson('/api/medico/login', ['email' => $medico->usuario->email, 'password' => 'secreto123'])->json('token');
        $tokenPaciente = $this->postJson('/api/paciente/login', ['email' => $paciente->email, 'password' => 'secreto123'])->json('token');

        $this->conToken($tokenAdmin, 'GET', '/api/admin/dashboard')->assertOk()->assertJsonStructure(['data' => ['estadisticas']]);
        $this->conToken($tokenAdmin, 'GET', '/api/citas')->assertOk();
        $this->conToken($tokenAdmin, 'GET', '/api/medico/citas')->assertForbidden();
        $this->conToken($tokenAdmin, 'GET', '/api/paciente/citas')->assertForbidden();

        $this->conToken($tokenMedico, 'GET', '/api/medico/citas')->assertOk();
        $this->conToken($tokenMedico, 'GET', '/api/citas')->assertForbidden();
        $this->conToken($tokenMedico, 'GET', '/api/paciente/citas')->assertForbidden();

        $this->conToken($tokenPaciente, 'GET', '/api/paciente/citas')->assertOk();
        $this->conToken($tokenPaciente, 'GET', '/api/medicos')->assertForbidden();
        $this->conToken($tokenPaciente, 'GET', '/api/medico/recetas')->assertForbidden();

        $this->app['auth']->forgetGuards();
        $this->withoutToken()->getJson('/api/paciente/citas')->assertUnauthorized();

        // Al cerrar sesión el token deja de funcionar
        $this->conToken($tokenPaciente, 'POST', '/api/paciente/logout')->assertOk();
        $this->conToken($tokenPaciente, 'GET', '/api/paciente/citas')->assertUnauthorized();
    }

    public function test_el_medico_solo_gestiona_sus_citas_expedientes_y_recetas(): void
    {
        $medico = $this->crearMedico('medico@example.test', 'CED-1');
        $otroMedico = $this->crearMedico('otro@example.test', 'CED-2');
        $paciente = $this->crearPaciente('paciente@example.test');
        $sinCitas = $this->crearPaciente('sincitas@example.test');
        $producto = $this->crearProducto();

        $propia = $this->crearCita($paciente, $medico);
        $ajena = $this->crearCita($paciente, $otroMedico);

        $token = $this->postJson('/api/medico/login', ['email' => $medico->usuario->email, 'password' => 'secreto123'])->json('token');

        $this->conToken($token, 'GET', '/api/medico/citas')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id_cita', $propia->id_cita);
        $this->conToken($token, 'GET', "/api/medico/citas/{$ajena->id_cita}")->assertNotFound();
        $this->conToken($token, 'PUT', "/api/medico/citas/{$ajena->id_cita}", ['estado' => 'Confirmada'])->assertNotFound();

        $this->conToken($token, 'PUT', "/api/medico/citas/{$propia->id_cita}", ['estado' => 'Confirmada'])
            ->assertOk()->assertJsonPath('data.estado', 'Confirmada');

        $this->conToken($token, 'PUT', "/api/medico/citas/{$propia->id_cita}/expediente", [
            'diagnostico' => 'Faringitis',
            'tratamiento' => 'Reposo e hidratación',
        ])->assertCreated()->assertJsonPath('data.estado', 'Completada');

        $this->conToken($token, 'GET', "/api/medico/citas/{$propia->id_cita}/expediente")
            ->assertOk()->assertJsonPath('data.diagnostico', 'Faringitis');

        $this->conToken($token, 'GET', '/api/medico/pacientes')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id_usuario', $paciente->id_usuario);

        $receta = [
            'paciente_id'   => $paciente->id_usuario,
            'fecha_emision' => today()->toDateString(),
            'estado'        => 'Vigente',
            'productos'     => [[
                'producto_id' => $producto->id,
                'cantidad'    => 2,
                'dosis'       => '1 tableta',
                'frecuencia'  => 'Cada 8 horas',
                'duracion'    => '5 días',
            ]],
        ];

        $idReceta = $this->conToken($token, 'POST', '/api/medico/recetas', $receta)
            ->assertCreated()
            ->assertJsonPath('data.medico_id', $medico->id_medico)
            ->json('data.id_receta');

        // No puede emitir recetas a pacientes que no tienen citas con él
        $this->conToken($token, 'POST', '/api/medico/recetas', [...$receta, 'paciente_id' => $sinCitas->id_usuario])
            ->assertUnprocessable()->assertJsonValidationErrors('paciente_id');

        // Anular = cambiar el estado; no existe DELETE
        $this->conToken($token, 'PUT', "/api/medico/recetas/{$idReceta}", [...$receta, 'estado' => 'Cancelada'])
            ->assertOk()->assertJsonPath('data.estado', 'Cancelada');
        $this->conToken($token, 'DELETE', "/api/medico/recetas/{$idReceta}")->assertStatus(405);

        $recetaAjena = Receta::create([
            'medico_id' => $otroMedico->id_medico, 'paciente_id' => $paciente->id_usuario,
            'fecha_emision' => now(), 'estado' => 'Vigente',
        ]);
        $this->conToken($token, 'GET', "/api/medico/recetas/{$recetaAjena->id_receta}")->assertNotFound();
    }

    public function test_el_paciente_solo_ve_lo_suyo_y_elige_que_comprar(): void
    {
        $medico = $this->crearMedico('medico@example.test', 'CED-1');
        $paciente = $this->crearPaciente('paciente@example.test');
        $otroPaciente = $this->crearPaciente('otro@example.test');
        $producto = $this->crearProducto(stock: 3);

        $this->crearCita($paciente, $medico);
        $this->crearCita($otroPaciente, $medico);

        $receta = Receta::create([
            'medico_id' => $medico->id_medico, 'paciente_id' => $paciente->id_usuario,
            'fecha_emision' => now(), 'estado' => 'Vigente',
        ]);
        $detalle = $receta->detalles()->create([
            'producto_id' => $producto->id, 'cantidad' => 5,
            'dosis' => '1 tableta', 'frecuencia' => 'Cada 8 horas', 'duracion' => '5 días',
        ]);
        $recetaAjena = Receta::create([
            'medico_id' => $medico->id_medico, 'paciente_id' => $otroPaciente->id_usuario,
            'fecha_emision' => now(), 'estado' => 'Vigente',
        ]);

        $token = $this->postJson('/api/paciente/login', ['email' => $paciente->email, 'password' => 'secreto123'])->json('token');

        $this->conToken($token, 'GET', '/api/paciente/citas')->assertOk()->assertJsonCount(1, 'data');
        $this->conToken($token, 'GET', '/api/paciente/resumen')->assertOk()->assertJsonPath('data.totales.recetas', 1);
        $this->conToken($token, 'GET', '/api/paciente/recetas')->assertOk()->assertJsonCount(1, 'data');
        $this->conToken($token, 'GET', "/api/paciente/recetas/{$recetaAjena->id_receta}")->assertNotFound();

        // Lo recetado son 5, pero solo hay 3 en existencia
        $this->conToken($token, 'PUT', "/api/paciente/recetas/{$receta->id_receta}/compra", [
            'detalles' => [$detalle->id_detalle => ['comprar' => true, 'cantidad_compra' => 4]],
        ])->assertUnprocessable();

        $this->conToken($token, 'PUT', "/api/paciente/recetas/{$receta->id_receta}/compra", [
            'detalles' => [$detalle->id_detalle => ['comprar' => true, 'cantidad_compra' => 3]],
        ])->assertOk()
            ->assertJsonPath('data.productos_seleccionados', 1)
            ->assertJsonPath('data.detalles.0.cantidad_compra', 3);

        $this->assertSame(5, (int) $detalle->fresh()->cantidad); // lo indicado por el médico no cambia
        $this->conToken($token, 'DELETE', "/api/paciente/recetas/{$receta->id_receta}")->assertStatus(405);
    }

    public function test_un_paciente_nuevo_se_registra_y_recibe_token(): void
    {
        $this->postJson('/api/paciente/registro', [
            'nombre' => 'Ana', 'apellido' => 'López', 'email' => 'ana@example.test',
            'password' => 'secreto123', 'password_confirmation' => 'secreto123',
        ])->assertCreated()->assertJsonPath('rol', 'paciente')->assertJsonStructure(['token']);

        $this->assertDatabaseHas('usuarios', ['email' => 'ana@example.test', 'activo' => true]);
    }

    // ------------------------------------------------------------------

    // Cada petición usa solo el token indicado (se limpia la sesión del guard entre peticiones)
    private function conToken(string $token, string $metodo, string $uri, array $datos = []): TestResponse
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($token)->json($metodo, $uri, $datos);
    }

    private function crearAdmin(): Administrador
    {
        return Administrador::create([
            'nombre_completo' => 'Administrador',
            'nombre_usuario'  => 'admin',
            'password'        => 'secreto123',
            'activo'          => true,
            'nivel'           => 3,
        ]);
    }

    private function crearPaciente(string $email): Usuario
    {
        return Usuario::create([
            'nombre' => 'Paciente', 'apellido' => 'Prueba', 'email' => $email,
            'password' => Hash::make('secreto123'), 'activo' => true, 'fecha_registro' => now(),
        ]);
    }

    private function crearMedico(string $email, string $cedula): Medico
    {
        $especialidad = Especialidad::firstOrCreate(['nombre' => 'Medicina general']);

        return Medico::create([
            'id_usuario'      => $this->crearPaciente($email)->id_usuario,
            'cedula'          => $cedula,
            'especialidad_id' => $especialidad->id_especialidad,
            'consultorio'     => '101',
        ])->load('usuario');
    }

    private function crearCita(Usuario $paciente, Medico $medico): Cita
    {
        return Cita::create([
            'paciente_id' => $paciente->id_usuario,
            'medico_id'   => $medico->id_medico,
            'fecha'       => today()->addDay(),
            'hora'        => '10:00',
            'estado'      => 'Pendiente',
        ]);
    }

    private function crearProducto(int $stock = 20): Producto
    {
        return Producto::forceCreate([
            'nombre' => 'Paracetamol', 'precio' => 25.50, 'stock' => $stock, 'categoria' => 'Analgésicos',
        ]);
    }
}
