<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\ExpedienteMedico;
use App\Models\Receta;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Portal del paciente (cliente). Solo muestra información propia:
 * sus citas, sus expedientes médicos y sus recetas. En las recetas puede elegir
 * qué productos comprar y en qué cantidad, pero no puede eliminarlas ni cambiar
 * lo que indicó el médico.
 */
class ClientePortalController extends Controller
{
    private function cliente(): Usuario
    {
        return Auth::guard('cliente')->user();
    }

    public function index(): View
    {
        $id = $this->cliente()->id_usuario;

        $proximasCitas = Cita::with(['medico.usuario', 'medico.especialidad'])
            ->where('paciente_id', $id)
            ->whereIn('estado', ['Pendiente', 'Confirmada'])
            ->whereDate('fecha', '>=', today())
            ->orderBy('fecha')
            ->orderBy('hora')
            ->limit(5)
            ->get();

        $recetasVigentes = Receta::with(['medico.usuario', 'detalles.producto'])
            ->where('paciente_id', $id)
            ->where('estado', 'Vigente')
            ->latest('fecha_emision')
            ->get();

        $ultimosExpedientes = ExpedienteMedico::with('cita.medico.usuario')
            ->whereHas('cita', fn ($q) => $q->where('paciente_id', $id))
            ->latest('fecha')
            ->limit(3)
            ->get();

        $totales = [
            'Citas'       => Cita::where('paciente_id', $id)->count(),
            'Expedientes' => ExpedienteMedico::whereHas('cita', fn ($q) => $q->where('paciente_id', $id))->count(),
            'Recetas'     => Receta::where('paciente_id', $id)->count(),
        ];

        return view('cliente.dashboard', compact('proximasCitas', 'recetasVigentes', 'ultimosExpedientes', 'totales'));
    }

    // ------------------------------------------------------------------
    // Citas
    // ------------------------------------------------------------------

    public function citas(): View
    {
        $citas = Cita::with(['medico.usuario', 'medico.especialidad', 'expediente'])
            ->where('paciente_id', $this->cliente()->id_usuario)
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->get();

        return view('cliente.citas.index', compact('citas'));
    }

    public function cita(int $id): View
    {
        $cita = Cita::with(['medico.usuario', 'medico.especialidad', 'expediente'])
            ->where('paciente_id', $this->cliente()->id_usuario)
            ->findOrFail($id);

        return view('cliente.citas.show', compact('cita'));
    }

    // ------------------------------------------------------------------
    // Expedientes médicos
    // ------------------------------------------------------------------

    public function expedientes(): View
    {
        $expedientes = ExpedienteMedico::with(['cita.medico.usuario', 'cita.medico.especialidad'])
            ->whereHas('cita', fn ($q) => $q->where('paciente_id', $this->cliente()->id_usuario))
            ->latest('fecha')
            ->get();

        return view('cliente.expedientes.index', compact('expedientes'));
    }

    public function expediente(int $id): View
    {
        $expediente = ExpedienteMedico::with(['cita.medico.usuario', 'cita.medico.especialidad'])
            ->whereHas('cita', fn ($q) => $q->where('paciente_id', $this->cliente()->id_usuario))
            ->findOrFail($id);

        return view('cliente.expedientes.show', compact('expediente'));
    }

    // ------------------------------------------------------------------
    // Recetas
    // ------------------------------------------------------------------

    private function recetaPropia(int $id): Receta
    {
        return Receta::with(['medico.usuario', 'medico.especialidad', 'paciente', 'detalles.producto'])
            ->where('paciente_id', $this->cliente()->id_usuario)
            ->findOrFail($id);
    }

    public function recetas(): View
    {
        $recetas = Receta::with(['medico.usuario', 'detalles.producto'])
            ->where('paciente_id', $this->cliente()->id_usuario)
            ->latest('fecha_emision')
            ->get();

        return view('cliente.recetas.index', compact('recetas'));
    }

    public function receta(int $id): View
    {
        $receta = $this->recetaPropia($id);

        return view('cliente.recetas.show', compact('receta'));
    }

    // Formulario para elegir qué productos comprar y cuántos
    public function editarCompra(int $id): View|RedirectResponse
    {
        $receta = $this->recetaPropia($id);

        if (!$receta->esEditablePorPaciente()) {
            return redirect()->route('cliente.recetas.show', $receta->id_receta)
                ->with('error', "Esta receta está \"{$receta->estado}\" y ya no se puede modificar.");
        }

        return view('cliente.recetas.edit', compact('receta'));
    }

    // Solo se guardan "comprar" y "cantidad_compra"; lo indicado por el médico no cambia
    public function actualizarCompra(Request $request, int $id): RedirectResponse
    {
        $receta = $this->recetaPropia($id);

        if (!$receta->esEditablePorPaciente()) {
            return redirect()->route('cliente.recetas.show', $receta->id_receta)
                ->with('error', "Esta receta está \"{$receta->estado}\" y ya no se puede modificar.");
        }

        $entrada = $request->input('detalles', []);
        $entrada = is_array($entrada) ? $entrada : [];
        $errores = [];
        $cambios = [];

        foreach ($receta->detalles as $detalle) {
            $fila = $entrada[$detalle->id_detalle] ?? [];
            $comprar = filter_var($fila['comprar'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $nombre = $detalle->producto->nombre ?? 'Producto';

            if (!$comprar) {
                $cambios[$detalle->id_detalle] = ['comprar' => false, 'cantidad_compra' => 0];
                continue;
            }

            $maximo = $detalle->maximo_compra;
            $cantidad = filter_var($fila['cantidad_compra'] ?? null, FILTER_VALIDATE_INT);

            if ($maximo < 1) {
                $errores["detalles.{$detalle->id_detalle}.comprar"] = "\"{$nombre}\" no está disponible en este momento.";
            } elseif ($cantidad === false || $cantidad < 1) {
                $errores["detalles.{$detalle->id_detalle}.cantidad_compra"] = "Indica una cantidad válida para \"{$nombre}\".";
            } elseif ($cantidad > $maximo) {
                $errores["detalles.{$detalle->id_detalle}.cantidad_compra"] = $maximo < $detalle->cantidad
                    ? "Solo hay {$maximo} unidad(es) de \"{$nombre}\" en existencia."
                    : "No puedes comprar más de lo recetado de \"{$nombre}\" ({$maximo}).";
            } else {
                $cambios[$detalle->id_detalle] = ['comprar' => true, 'cantidad_compra' => $cantidad];
            }
        }

        if ($errores) {
            throw ValidationException::withMessages($errores);
        }

        DB::transaction(function () use ($receta, $cambios) {
            foreach ($receta->detalles as $detalle) {
                $detalle->update($cambios[$detalle->id_detalle]);
            }
        });

        $receta->load('detalles.producto');

        return redirect()->route('cliente.recetas.show', $receta->id_receta)
            ->with('status', $receta->productos_seleccionados > 0
                ? 'Selección guardada. Total a comprar: $' . number_format($receta->total_compra, 2)
                : 'Selección guardada. No elegiste productos para comprar.');
    }
}
