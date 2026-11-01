<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\ExpedienteMedico;
use App\Models\Medico;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Portal médico en la API (token de Sanctum, respuestas JSON).
 */
class DoctorPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_active_doctors_can_sign_in_to_the_clinical_portal(): void
    {
        [$doctor] = $this->createDoctor();

        $token = $this->postJson('/api/medico/login', [
            'email'    => $doctor->email,
            'password' => 'doctor-password',
        ])->assertOk()->json('token');

        $this->withToken($token)->getJson('/api/medico/citas')->assertOk();

        // El token del médico no sirve en el panel administrativo
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/admin/dashboard')->assertForbidden();

        $doctor->update(['activo' => false]);

        $this->postJson('/api/medico/login', [
            'email'    => $doctor->email,
            'password' => 'doctor-password',
        ])->assertForbidden()->assertJsonPath('mensaje', 'La cuenta está deshabilitada.');
    }

    public function test_doctors_only_see_and_update_their_own_appointments_and_records(): void
    {
        [$doctorUser, $medico] = $this->createDoctor('doctor@example.test', 'DOC-1');
        [, $otherMedico] = $this->createDoctor('other@example.test', 'DOC-2');
        $patient = $this->createPatient('patient@example.test');
        $ownAppointment = $this->createAppointment($patient, $medico);
        $otherAppointment = $this->createAppointment($patient, $otherMedico);

        $token = $this->postJson('/api/medico/login', [
            'email'    => $doctorUser->email,
            'password' => 'doctor-password',
        ])->json('token');

        $this->withToken($token)->getJson('/api/medico/citas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id_cita', $ownAppointment->id_cita);

        $this->putJson("/api/medico/citas/{$otherAppointment->id_cita}", ['estado' => 'Completada'])
            ->assertNotFound();

        $this->putJson("/api/medico/citas/{$ownAppointment->id_cita}", ['estado' => 'Completada'])
            ->assertOk();
        $this->assertSame('Completada', $ownAppointment->fresh()->estado);

        $this->putJson("/api/medico/citas/{$ownAppointment->id_cita}/expediente", [
            'diagnostico'   => 'Diagnóstico de prueba',
            'tratamiento'   => 'Tratamiento de prueba',
            'observaciones' => 'Nota clínica de prueba',
        ])->assertCreated();
        $this->assertDatabaseHas('expediente_medico', [
            'id_cita'     => $ownAppointment->id_cita,
            'diagnostico' => 'Diagnóstico de prueba',
        ]);

        $this->getJson("/api/medico/citas/{$ownAppointment->id_cita}/expediente")
            ->assertOk()
            ->assertJsonPath('data.diagnostico', 'Diagnóstico de prueba');

        $this->putJson("/api/medico/citas/{$otherAppointment->id_cita}/expediente", [
            'diagnostico' => 'No autorizado',
            'tratamiento' => 'No autorizado',
        ])->assertNotFound();
        $this->assertSame(1, ExpedienteMedico::count());
    }

    private function createDoctor(string $email = 'doctor@example.test', string $cedula = 'DOC-1'): array
    {
        $usuario = Usuario::create([
            'nombre'   => 'Dra.',
            'apellido' => 'Prueba',
            'email'    => $email,
            'password' => Hash::make('doctor-password'),
            'activo'   => true,
        ]);

        $especialidad = Especialidad::firstOrCreate(
            ['nombre' => 'Medicina general'],
            ['descripcion' => 'Especialidad de prueba']
        );

        $medico = Medico::create([
            'id_usuario'      => $usuario->id_usuario,
            'cedula'          => $cedula,
            'especialidad_id' => $especialidad->id_especialidad,
            'consultorio'     => 'Consultorio 1',
        ]);

        return [$usuario, $medico];
    }

    private function createPatient(string $email): Usuario
    {
        return Usuario::create([
            'nombre'   => 'Paciente',
            'apellido' => 'Prueba',
            'email'    => $email,
            'password' => Hash::make('patient-password'),
            'activo'   => true,
        ]);
    }

    private function createAppointment(Usuario $patient, Medico $medico): Cita
    {
        return Cita::create([
            'paciente_id' => $patient->id_usuario,
            'medico_id'   => $medico->id_medico,
            'fecha'       => today()->addDay(),
            'hora'        => '09:00:00',
            'estado'      => 'Pendiente',
            'motivo'      => 'Consulta de prueba',
        ]);
    }
}
