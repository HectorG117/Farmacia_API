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

class DoctorPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_active_doctors_can_sign_in_to_the_clinical_portal(): void
    {
        [$doctor] = $this->createDoctor();

        $this->post(route('doctor.login.store'), [
            'email' => $doctor->email,
            'password' => 'doctor-password',
        ])->assertRedirect(route('doctor.dashboard'));

        $this->assertAuthenticatedAs($doctor, 'doctor');
        $this->assertGuest('admin');
        $this->get(route('doctor.dashboard'))->assertOk();
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_doctors_only_see_and_update_their_own_appointments_and_records(): void
    {
        [, $medico] = $this->createDoctor('doctor@example.test', 'DOC-1');
        [, $otherMedico] = $this->createDoctor('other@example.test', 'DOC-2');
        $patient = $this->createPatient('patient@example.test');
        $ownAppointment = $this->createAppointment($patient, $medico);
        $otherAppointment = $this->createAppointment($patient, $otherMedico);

        $this->actingAs($medico->usuario, 'doctor')
            ->get(route('doctor.dashboard'))
            ->assertOk()
            ->assertSee(route('doctor.citas.update', $ownAppointment->id_cita), false)
            ->assertDontSee(route('doctor.citas.update', $otherAppointment->id_cita), false);

        $this->put(route('doctor.citas.update', $otherAppointment->id_cita), [
            'estado' => 'Completada',
        ])->assertNotFound();

        $this->put(route('doctor.citas.update', $ownAppointment->id_cita), [
            'estado' => 'Completada',
        ])->assertRedirect(route('doctor.dashboard'));
        $this->assertSame('Completada', $ownAppointment->fresh()->estado);

        $this->put(route('doctor.expedientes.update', $ownAppointment->id_cita), [
            'diagnostico' => 'Diagnóstico de prueba',
            'tratamiento' => 'Tratamiento de prueba',
            'observaciones' => 'Nota clínica de prueba',
        ])->assertRedirect(route('doctor.dashboard'));
        $this->assertDatabaseHas('expediente_medico', [
            'id_cita' => $ownAppointment->id_cita,
            'diagnostico' => 'Diagnóstico de prueba',
        ]);
        $this->get(route('doctor.expedientes.edit', $ownAppointment->id_cita))
            ->assertOk()
            ->assertSee('Diagnóstico de prueba');

        $this->put(route('doctor.expedientes.update', $otherAppointment->id_cita), [
            'diagnostico' => 'No autorizado',
            'tratamiento' => 'No autorizado',
        ])->assertNotFound();
        $this->assertSame(1, ExpedienteMedico::count());
    }

    private function createDoctor(string $email = 'doctor@example.test', string $cedula = 'DOC-1'): array
    {
        $usuario = Usuario::create([
            'nombre' => 'Dra.',
            'apellido' => 'Prueba',
            'email' => $email,
            'password' => Hash::make('doctor-password'),
            'activo' => true,
        ]);

        $especialidad = Especialidad::firstOrCreate(
            ['nombre' => 'Medicina general'],
            ['descripcion' => 'Especialidad de prueba']
        );

        $medico = Medico::create([
            'id_usuario' => $usuario->id_usuario,
            'cedula' => $cedula,
            'especialidad_id' => $especialidad->id_especialidad,
            'consultorio' => 'Consultorio 1',
        ]);

        return [$usuario, $medico];
    }

    private function createPatient(string $email): Usuario
    {
        return Usuario::create([
            'nombre' => 'Paciente',
            'apellido' => 'Prueba',
            'email' => $email,
            'password' => Hash::make('patient-password'),
            'activo' => true,
        ]);
    }

    private function createAppointment(Usuario $patient, Medico $medico): Cita
    {
        return Cita::create([
            'paciente_id' => $patient->id_usuario,
            'medico_id' => $medico->id_medico,
            'fecha' => '2026-10-23',
            'hora' => '09:00:00',
            'estado' => 'Pendiente',
            'motivo' => 'Consulta de prueba',
        ]);
    }
}
