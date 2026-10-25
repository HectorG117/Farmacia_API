<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\ExpedienteMedico;
use App\Models\Medico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DoctorPortalController extends Controller
{
    private function medicoActual(): Medico
    {
        return Medico::where('id_usuario', Auth::guard('doctor')->id())->firstOrFail();
    }

    // Cita que pertenece al médico que inició sesión (si no es suya, 404)
    private function citaDelMedico(int $id): Cita
    {
        return Cita::with(['paciente', 'expediente'])
            ->where('medico_id', $this->medicoActual()->id_medico)
            ->findOrFail($id);
    }

    public function index(): View
    {
        $citas = Cita::with(['paciente', 'expediente'])
            ->where('medico_id', $this->medicoActual()->id_medico)
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->get();

        return view('doctor.dashboard', compact('citas'));
    }

    public function updateAppointment(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'estado' => ['required', Rule::in(Cita::ESTADOS)],
        ]);

        $cita = $this->citaDelMedico($id);

        // Misma regla que el panel administrativo: una cita con expediente ya fue completada
        if ($cita->expediente && $data['estado'] !== 'Completada') {
            return redirect()->route('doctor.dashboard')
                ->with('error', 'Esta cita ya tiene expediente médico, su estado debe ser "Completada".');
        }

        $cita->update($data);

        return redirect()->route('doctor.dashboard')->with('status', 'Estado de la cita actualizado.');
    }

    public function editExpediente(int $id): View|RedirectResponse
    {
        $cita = $this->citaDelMedico($id);
        $expediente = $cita->expediente;

        if (!$expediente && $cita->estado === 'Cancelada') {
            return redirect()->route('doctor.dashboard')
                ->with('error', 'No se puede registrar un expediente para una cita cancelada.');
        }

        return view('doctor.expedientes.edit', compact('cita', 'expediente'));
    }

    public function saveExpediente(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'diagnostico'   => ['required', 'string', 'min:3', 'max:2000'],
            'tratamiento'   => ['required', 'string', 'min:3', 'max:2000'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);

        $cita = $this->citaDelMedico($id);

        if (!$cita->expediente && $cita->estado === 'Cancelada') {
            return redirect()->route('doctor.dashboard')
                ->with('error', 'No se puede registrar un expediente para una cita cancelada.');
        }

        DB::transaction(function () use ($cita, $data) {
            if ($cita->expediente) {
                // Al editar se conserva la fecha original de atención
                $cita->expediente->update($data);
            } else {
                ExpedienteMedico::create([...$data, 'id_cita' => $cita->id_cita, 'fecha' => now()]);
            }

            // Igual que en el panel administrativo: con expediente, la cita queda completada
            $cita->update(['estado' => 'Completada']);
        });

        return redirect()->route('doctor.dashboard')
            ->with('status', 'Expediente médico guardado. La cita se marcó como completada.');
    }
}
