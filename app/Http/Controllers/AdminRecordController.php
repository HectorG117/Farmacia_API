<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CarritoDetalle;
use App\Models\HistorialTransaccion;
use App\Models\ServicioMedico;
use App\Models\User;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminRecordController extends Controller
{
    public function index(Request $request)
    {
        $module = $this->moduleFromRoute($request);
        $definition = $this->definition($module);
        $records = $definition['model']::with($definition['with'])
            ->orderByDesc($definition['key'])
            ->paginate(15);

        return view('admin.records.index', compact('definition', 'module', 'records'));
    }

    public function create(Request $request)
    {
        $module = $this->moduleFromRoute($request);
        $definition = $this->definition($module);
        $record = null;
        $options = $this->options($definition);

        return view('admin.records.form', compact('definition', 'module', 'record', 'options'));
    }

    public function store(Request $request)
    {
        $module = $this->moduleFromRoute($request);
        $definition = $this->definition($module);
        $data = $this->validatedData($request, $definition);

        if ($module === 'usuarios' && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $definition['model']::create($data);

        return redirect()->route("admin.{$module}.index")
            ->with('success', "{$definition['title']} creado correctamente.");
    }

    public function edit(Request $request, int $id)
    {
        $module = $this->moduleFromRoute($request);
        $definition = $this->definition($module);
        $record = $definition['model']::findOrFail($id);
        $options = $this->options($definition);

        return view('admin.records.form', compact('definition', 'module', 'record', 'options'));
    }

    public function update(Request $request, int $id)
    {
        $module = $this->moduleFromRoute($request);
        $definition = $this->definition($module);
        $record = $definition['model']::findOrFail($id);
        $data = $this->validatedData($request, $definition, $record);

        if (in_array($module, ['usuarios', 'users'], true)) {
            if (!empty($data['password'])) {
                if ($module === 'usuarios') {
                    $data['password'] = Hash::make($data['password']);
                }
            } else {
                unset($data['password']);
            }
        }

        $record->update($data);

        return redirect()->route("admin.{$module}.index")
            ->with('success', "{$definition['title']} actualizado correctamente.");
    }

    public function destroy(Request $request, int $id)
    {
        $module = $this->moduleFromRoute($request);
        $definition = $this->definition($module);
        $record = $definition['model']::findOrFail($id);

        if ($record instanceof Carrito && $record->transacciones()->exists()) {
            return redirect()->route("admin.{$module}.index")
                ->with('error', 'No se puede eliminar este carrito porque tiene transacciones asociadas.');
        }

        $record->delete();

        return redirect()->route("admin.{$module}.index")
            ->with('success', "{$definition['title']} eliminado correctamente.");
    }

    private function validatedData(Request $request, array $definition, $record = null): array
    {
        $rules = [];

        foreach ($definition['fields'] as $field) {
            $fieldRules = $field['rules'];

            if ($record && $field['name'] === 'password') {
                $fieldRules = ['nullable', 'string', 'min:8'];
            }

            if (!empty($field['unique'])) {
                $fieldRules[] = Rule::unique($definition['model']::make()->getTable(), $field['name'])
                    ->ignore($record?->getKey(), $definition['key']);
            }

            $rules[$field['name']] = $fieldRules;
        }

        return $request->validate($rules);
    }

    private function moduleFromRoute(Request $request): string
    {
        $routeName = $request->route()?->getName() ?? '';
        $segments = explode('.', $routeName);

        return $segments[1] ?? '';
    }

    private function options(array $definition): array
    {
        $options = [];

        foreach ($definition['fields'] as $field) {
            if (!isset($field['relation'])) {
                $options[$field['name']] = $field['options'] ?? [];
                continue;
            }

            $relation = $field['relation'];
            $options[$field['name']] = $relation['model']::query()
                ->orderBy($relation['label_column'])
                ->get()
                ->mapWithKeys(fn ($item) => [$item->getAttribute($relation['key']) => ($relation['label'])($item)])
                ->all();
        }

        return $options;
    }

    private function definition(string $module): array
    {
        $usuarioLabel = fn (Usuario $usuario) => trim($usuario->nombre . ' ' . $usuario->apellido);
        $carritoLabel = fn (Carrito $carrito) => "Carrito #{$carrito->id_carrito}";

        $definitions = [
            'usuarios' => [
                'title' => 'Usuarios',
                'model' => Usuario::class,
                'key' => 'id_usuario',
                'with' => [],
                'fields' => [
                    ['name' => 'nombre', 'label' => 'Nombre', 'type' => 'text', 'rules' => ['required', 'string', 'max:100']],
                    ['name' => 'apellido', 'label' => 'Apellido', 'type' => 'text', 'rules' => ['required', 'string', 'max:100']],
                    ['name' => 'email', 'label' => 'Correo electronico', 'type' => 'email', 'rules' => ['required', 'email', 'max:150'], 'unique' => true],
                    ['name' => 'telefono', 'label' => 'Telefono', 'type' => 'tel', 'rules' => ['nullable', 'string', 'max:20']],
                    ['name' => 'password', 'label' => 'Contrasena', 'type' => 'password', 'rules' => ['required', 'string', 'min:8']],
                    ['name' => 'activo', 'label' => 'Estado', 'type' => 'select', 'options' => [1 => 'Activo', 0 => 'Inactivo'], 'rules' => ['required', 'boolean']],
                ],
                'columns' => [
                    ['label' => 'ID', 'value' => fn (Usuario $item) => $item->id_usuario],
                    ['label' => 'Nombre', 'value' => fn (Usuario $item) => $usuarioLabel($item)],
                    ['label' => 'Correo', 'value' => fn (Usuario $item) => $item->email],
                    ['label' => 'Telefono', 'value' => fn (Usuario $item) => $item->telefono ?: '—'],
                    ['label' => 'Estado', 'value' => fn (Usuario $item) => $item->activo ? 'Activo' : 'Inactivo'],
                ],
            ],
            'carritos' => [
                'title' => 'Carritos',
                'model' => Carrito::class,
                'key' => 'id_carrito',
                'with' => ['usuario'],
                'fields' => [
                    ['name' => 'id_usuario', 'label' => 'Usuario', 'type' => 'select', 'relation' => ['model' => Usuario::class, 'key' => 'id_usuario', 'label_column' => 'nombre', 'label' => $usuarioLabel], 'rules' => ['required', 'exists:usuarios,id_usuario']],
                    ['name' => 'fecha', 'label' => 'Fecha', 'type' => 'datetime-local', 'rules' => ['required', 'date']],
                    ['name' => 'estado', 'label' => 'Estado', 'type' => 'select', 'options' => ['Activo' => 'Activo', 'Completado' => 'Completado', 'Abandonado' => 'Abandonado'], 'rules' => ['required', 'string', 'max:30']],
                ],
                'columns' => [
                    ['label' => 'ID', 'value' => fn (Carrito $item) => $item->id_carrito],
                    ['label' => 'Usuario', 'value' => fn (Carrito $item) => $item->usuario ? $usuarioLabel($item->usuario) : '—'],
                    ['label' => 'Fecha', 'value' => fn (Carrito $item) => $item->fecha?->format('d/m/Y H:i')],
                    ['label' => 'Estado', 'value' => fn (Carrito $item) => $item->estado],
                ],
            ],
            'carrito-detalles' => [
                'title' => 'Detalles de carrito',
                'model' => CarritoDetalle::class,
                'key' => 'id_detalle',
                'with' => ['carrito', 'servicio'],
                'fields' => [
                    ['name' => 'id_carrito', 'label' => 'Carrito', 'type' => 'select', 'relation' => ['model' => Carrito::class, 'key' => 'id_carrito', 'label_column' => 'id_carrito', 'label' => $carritoLabel], 'rules' => ['required', 'exists:carrito,id_carrito']],
                    ['name' => 'id_servicio', 'label' => 'Servicio medico', 'type' => 'select', 'relation' => ['model' => ServicioMedico::class, 'key' => 'id_servicio', 'label_column' => 'nombre', 'label' => fn (ServicioMedico $item) => $item->nombre], 'rules' => ['required', 'exists:servicios_medicos,id_servicio']],
                    ['name' => 'cantidad', 'label' => 'Cantidad', 'type' => 'number', 'rules' => ['required', 'integer', 'min:1']],
                ],
                'columns' => [
                    ['label' => 'ID', 'value' => fn (CarritoDetalle $item) => $item->id_detalle],
                    ['label' => 'Carrito', 'value' => fn (CarritoDetalle $item) => $item->carrito ? $carritoLabel($item->carrito) : '—'],
                    ['label' => 'Servicio', 'value' => fn (CarritoDetalle $item) => $item->servicio?->nombre ?? '—'],
                    ['label' => 'Cantidad', 'value' => fn (CarritoDetalle $item) => $item->cantidad],
                ],
            ],
            'historial-transacciones' => [
                'title' => 'Historial de transacciones',
                'model' => HistorialTransaccion::class,
                'key' => 'id_transaccion',
                'with' => ['usuario', 'carrito'],
                'fields' => [
                    ['name' => 'id_usuario', 'label' => 'Usuario', 'type' => 'select', 'relation' => ['model' => Usuario::class, 'key' => 'id_usuario', 'label_column' => 'nombre', 'label' => $usuarioLabel], 'rules' => ['required', 'exists:usuarios,id_usuario']],
                    ['name' => 'id_carrito', 'label' => 'Carrito', 'type' => 'select', 'relation' => ['model' => Carrito::class, 'key' => 'id_carrito', 'label_column' => 'id_carrito', 'label' => $carritoLabel], 'rules' => ['required', 'exists:carrito,id_carrito']],
                    ['name' => 'total', 'label' => 'Total', 'type' => 'number', 'step' => '0.01', 'rules' => ['required', 'numeric', 'min:0']],
                    ['name' => 'estado', 'label' => 'Estado', 'type' => 'text', 'rules' => ['required', 'string', 'max:30']],
                    ['name' => 'fecha', 'label' => 'Fecha', 'type' => 'datetime-local', 'rules' => ['required', 'date']],
                ],
                'columns' => [
                    ['label' => 'ID', 'value' => fn (HistorialTransaccion $item) => $item->id_transaccion],
                    ['label' => 'Usuario', 'value' => fn (HistorialTransaccion $item) => $item->usuario ? $usuarioLabel($item->usuario) : '—'],
                    ['label' => 'Carrito', 'value' => fn (HistorialTransaccion $item) => $item->carrito ? $carritoLabel($item->carrito) : '—'],
                    ['label' => 'Total', 'value' => fn (HistorialTransaccion $item) => '$ ' . number_format((float) $item->total, 2)],
                    ['label' => 'Estado', 'value' => fn (HistorialTransaccion $item) => $item->estado],
                    ['label' => 'Fecha', 'value' => fn (HistorialTransaccion $item) => $item->fecha?->format('d/m/Y H:i')],
                ],
            ],
            'users' => [
                'title' => 'Cuentas de acceso',
                'model' => User::class,
                'key' => 'id',
                'with' => [],
                'fields' => [
                    ['name' => 'name', 'label' => 'Nombre', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                    ['name' => 'email', 'label' => 'Correo electronico', 'type' => 'email', 'rules' => ['required', 'email', 'max:255'], 'unique' => true],
                    ['name' => 'password', 'label' => 'Contrasena', 'type' => 'password', 'rules' => ['required', 'string', 'min:8']],
                ],
                'columns' => [
                    ['label' => 'ID', 'value' => fn (User $item) => $item->id],
                    ['label' => 'Nombre', 'value' => fn (User $item) => $item->name],
                    ['label' => 'Correo', 'value' => fn (User $item) => $item->email],
                    ['label' => 'Correo verificado', 'value' => fn (User $item) => $item->email_verified_at ? 'Si' : 'No'],
                ],
            ],
        ];

        abort_unless(isset($definitions[$module]), 404);

        return $definitions[$module];
    }
}