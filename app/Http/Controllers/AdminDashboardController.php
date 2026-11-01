<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Medico;
use App\Models\Producto;
use App\Models\Receta;
use App\Models\Usuario;
use Illuminate\View\View;

/**
 * Resumen del panel administrativo con datos reales de la base de datos.
 */
class AdminDashboardController extends Controller
{
    private const STOCK_BAJO = 10;

    public function __invoke(): View
    {
        $estadisticas = [
            ['label' => 'Pacientes activos', 'value' => Usuario::where('activo', true)->whereDoesntHave('medico')->count(), 'tone' => 'teal', 'route' => 'admin.usuarios.index'],
            ['label' => 'Médicos activos', 'value' => Medico::activos()->count(), 'tone' => 'orange', 'route' => 'admin.medicos.index'],
            ['label' => 'Citas de hoy', 'value' => Cita::whereDate('fecha', today())->where('estado', '!=', 'Cancelada')->count(), 'tone' => 'blue', 'route' => 'admin.citas.index', 'params' => ['fecha' => today()->format('Y-m-d')]],
            ['label' => 'Recetas vigentes', 'value' => Receta::where('estado', 'Vigente')->count(), 'tone' => 'violet', 'route' => 'admin.recetas-medicas.index'],
        ];

        $proximasCitas = Cita::with(['paciente', 'medico.usuario'])
            ->whereIn('estado', ['Pendiente', 'Confirmada'])
            ->whereDate('fecha', '>=', today())
            ->orderBy('fecha')
            ->orderBy('hora')
            ->limit(6)
            ->get();

        $ultimasRecetas = Receta::with(['paciente', 'medico.usuario', 'detalles.producto'])
            ->latest('fecha_emision')
            ->limit(5)
            ->get();

        $stockBajo = Producto::where('stock', '<', self::STOCK_BAJO)
            ->orderBy('stock')
            ->limit(6)
            ->get();

        return view('admin.dashboard', [
            'estadisticas'  => $estadisticas,
            'proximasCitas' => $proximasCitas,
            'ultimasRecetas'=> $ultimasRecetas,
            'stockBajo'     => $stockBajo,
            'limiteStock'   => self::STOCK_BAJO,
        ]);
    }
}
