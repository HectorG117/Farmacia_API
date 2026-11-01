<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GestionaRecetas;
use App\Models\Cita;
use App\Models\Medico;
use App\Models\Receta;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Recetas desde el portal médico: el médico solo ve y edita las recetas que él emitió.
 * No puede eliminarlas; para anular una receta la marca como "Cancelada".
 */
class DoctorRecetaController extends Controller
{
    use GestionaRecetas;

    private function medicoActual(): Medico
    {
        return Medico::with(['usuario', 'especialidad'])
            ->where('id_usuario', Auth::guard('doctor')->id())
            ->firstOrFail();
    }

    // Receta propia del médico (si no es suya, 404)
    private function recetaPropia(int $id): Receta
    {
        return Receta::with(['medico.usuario', 'medico.especialidad', 'paciente', 'detalles.producto'])
            ->where('medico_id', $this->medicoActual()->id_medico)
            ->findOrFail($id);
    }

    // Pacientes que han tenido al menos una cita con el médico (+ el de la receta al editar)
    private function pacientesDelMedico(Medico $medico, ?int $incluirId = null)
    {
        return Usuario::where(function ($q) use ($medico, $incluirId) {
            $q->whereHas('citas', fn ($c) => $c->where('medico_id', $medico->id_medico));
            if ($incluirId) {
                $q->orWhere('id_usuario', $incluirId);
            }
        })
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get();
    }

    public function index(): View
    {
        $recetas = Receta::with('paciente')
            ->withCount('detalles')
            ->where('medico_id', $this->medicoActual()->id_medico)
            ->latest('fecha_emision')
            ->get();

        return view('doctor.recetas.index', compact('recetas'));
    }

    // Puede llegar con ?cita=ID desde "Mis citas": se precarga el paciente y el diagnóstico
    public function create(Request $request): View|RedirectResponse
    {
        $medico = $this->medicoActual();
        $pacientes = $this->pacientesDelMedico($medico);

        if ($pacientes->isEmpty()) {
            return redirect()->route('doctor.dashboard')
                ->with('error', 'Aún no tienes pacientes con citas asignadas para poder emitir recetas.');
        }

        $diagnosticoSugerido = null;

        if ($request->filled('cita')) {
            $cita = Cita::with('expediente')
                ->where('medico_id', $medico->id_medico)
                ->find($request->integer('cita'));

            if ($cita) {
                $request->merge(['paciente_id' => $cita->paciente_id]);
                $diagnosticoSugerido = $cita->expediente
                    ? \Illuminate\Support\Str::limit($cita->expediente->diagnostico, 250, '')
                    : null;
            }
        }

        return view('doctor.recetas.create', [
            'medicoFijo'          => $medico,
            'pacientes'           => $pacientes,
            'productos'           => $this->productosParaFormulario(),
            'diagnosticoSugerido' => $diagnosticoSugerido,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $medico = $this->medicoActual();
        $datos = $this->validarReceta($request, soloProductosActivos: true, conMedico: false);

        if (!$this->pacientesDelMedico($medico)->contains('id_usuario', (int) $datos['paciente_id'])) {
            return back()->withInput()
                ->withErrors(['paciente_id' => 'Solo puedes emitir recetas a pacientes que tengan citas contigo.']);
        }

        $datos['medico_id'] = $medico->id_medico;

        $receta = DB::transaction(function () use ($datos) {
            $receta = Receta::create($this->datosEncabezado($datos));
            $this->guardarDetalles($receta, $datos);

            return $receta;
        });

        return redirect()->route('doctor.recetas.show', $receta->id_receta)
            ->with('status', 'Receta emitida correctamente.');
    }

    public function show(int $id): View
    {
        $receta = $this->recetaPropia($id);

        return view('doctor.recetas.show', compact('receta'));
    }

    public function edit(int $id): View
    {
        $receta = $this->recetaPropia($id);
        $medico = $this->medicoActual();

        return view('doctor.recetas.edit', [
            'receta'     => $receta,
            'medicoFijo' => $medico,
            'pacientes'  => $this->pacientesDelMedico($medico, $receta->paciente_id),
            'productos'  => $this->productosParaFormulario($receta),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $receta = $this->recetaPropia($id);
        $medico = $this->medicoActual();
        $datos = $this->validarReceta($request, soloProductosActivos: false, conMedico: false);

        if (!$this->pacientesDelMedico($medico, $receta->paciente_id)->contains('id_usuario', (int) $datos['paciente_id'])) {
            return back()->withInput()
                ->withErrors(['paciente_id' => 'Solo puedes emitir recetas a pacientes que tengan citas contigo.']);
        }

        $datos['medico_id'] = $medico->id_medico;

        DB::transaction(function () use ($receta, $datos) {
            $receta->update($this->datosEncabezado($datos));
            $this->guardarDetalles($receta, $datos);
        });

        return redirect()->route('doctor.recetas.show', $receta->id_receta)
            ->with('status', 'Receta actualizada correctamente.');
    }
}
