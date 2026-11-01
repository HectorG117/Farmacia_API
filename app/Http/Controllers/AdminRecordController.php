<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CarritoDetalle;
use App\Models\User;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * CRUD genérico (responde JSON para la API) de los módulos:
 *   usuarios, carritos, carrito-detalles y users (cuentas de acceso).
 *
 * El módulo se toma del nombre de la ruta: "admin.{modulo}.{accion}".
 * Rutas: GET/POST /api/{modulo}, GET/PUT/DELETE /api/{modulo}/{id}
 * El listado va paginado de 15 en 15 (?page=2).
 */
class AdminRecordController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $definition = $this->definition($this->moduleFromRoute($request));

        $records = $definition['model']::with($definition['with'])
            ->orderByDesc($definition['key'])
            ->paginate(15);

        return response()->json($records);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $definition = $this->definition($this->moduleFromRoute($request));
        $record = $definition['model']::with($definition['with'])->find($id);

        return $record
            ? response()->json(['data' => $record])
            : $this->notFound($definition);
    }

    public function store(Request $request): JsonResponse
    {
        $module = $this->moduleFromRoute($request);
        $definition = $this->definition($module);
        $data = $this->validatedData($request, $definition);

        // "users" usa el cast "hashed" del modelo; "usuarios" se cifra aquí
        if ($module === 'usuarios' && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $record = $definition['model']::create($data);

        return response()->json([
            'mensaje' => "{$definition['title']}: registro creado correctamente.",
            'data'    => $record->load($definition['with']),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $module = $this->moduleFromRoute($request);
        $definition = $this->definition($module);
        $record = $definition['model']::find($id);

        if (!$record) {
            return $this->notFound($definition);
        }

        $data = $this->validatedData($request, $definition, $record);

        // La contraseña es opcional al editar: si no se envía, se conserva la actual
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

        return response()->json([
            'mensaje' => "{$definition['title']}: registro actualizado correctamente.",
            'data'    => $record->fresh($definition['with']),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $definition = $this->definition($this->moduleFromRoute($request));
        $record = $definition['model']::find($id);

        if (!$record) {
            return $this->notFound($definition);
        }

        // Un usuario con perfil de médico se administra desde el módulo de Médicos
        if ($record instanceof Usuario && $record->medico()->exists()) {
            return response()->json([
                'mensaje' => 'Este usuario tiene perfil de médico. Elimina primero su perfil desde el módulo de Médicos.',
            ], 409);
        }

        if ($record instanceof Carrito && $record->transacciones()->exists()) {
            return response()->json([
                'mensaje' => 'No se puede eliminar este carrito porque tiene transacciones asociadas.',
            ], 409);
        }

        $record->delete();

        return response()->json(['mensaje' => "{$definition['title']}: registro eliminado correctamente."]);
    }

    // ------------------------------------------------------------------

    private function notFound(array $definition): JsonResponse
    {
        return response()->json(['mensaje' => "{$definition['title']}: el registro solicitado no existe."], 404);
    }

    private function validatedData(Request $request, array $definition, $record = null): array
    {
        $rules = [];

        foreach ($definition['fields'] as $name => $fieldRules) {
            if ($record && $name === 'password') {
                $fieldRules = ['nullable', 'string', 'min:8'];
            }

            if (in_array($name, $definition['unique'] ?? [], true)) {
                $fieldRules[] = Rule::unique($definition['model']::make()->getTable(), $name)
                    ->ignore($record?->getKey(), $definition['key']);
            }

            $rules[$name] = $fieldRules;
        }

        return $request->validate($rules);
    }

    private function moduleFromRoute(Request $request): string
    {
        $routeName = $request->route()?->getName() ?? '';
        $segments = explode('.', $routeName);

        return $segments[1] ?? '';
    }

    private function definition(string $module): array
    {
        $definitions = [
            'usuarios' => [
                'title'  => 'Usuarios',
                'model'  => Usuario::class,
                'key'    => 'id_usuario',
                'with'   => [],
                'unique' => ['email'],
                'fields' => [
                    'nombre'   => ['required', 'string', 'max:100'],
                    'apellido' => ['required', 'string', 'max:100'],
                    'email'    => ['required', 'email', 'max:150'],
                    'telefono' => ['nullable', 'string', 'max:20'],
                    'password' => ['required', 'string', 'min:8'],
                    'activo'   => ['required', 'boolean'],
                ],
            ],
            'carritos' => [
                'title'  => 'Carritos',
                'model'  => Carrito::class,
                'key'    => 'id_carrito',
                'with'   => ['usuario'],
                'fields' => [
                    'id_usuario' => ['required', 'exists:usuarios,id_usuario'],
                    'fecha'      => ['required', 'date'],
                    'estado'     => ['required', 'string', Rule::in(['Activo', 'Completado', 'Abandonado'])],
                ],
            ],
            'carrito-detalles' => [
                'title'  => 'Detalles de carrito',
                'model'  => CarritoDetalle::class,
                'key'    => 'id_detalle',
                'with'   => ['carrito', 'servicio'],
                'fields' => [
                    'id_carrito'  => ['required', 'exists:carrito,id_carrito'],
                    'id_servicio' => ['required', 'exists:servicios_medicos,id_servicio'],
                    'cantidad'    => ['required', 'integer', 'min:1'],
                ],
            ],
            'users' => [
                'title'  => 'Cuentas de acceso',
                'model'  => User::class,
                'key'    => 'id',
                'with'   => [],
                'unique' => ['email'],
                'fields' => [
                    'name'     => ['required', 'string', 'max:255'],
                    'email'    => ['required', 'email', 'max:255'],
                    'password' => ['required', 'string', 'min:8'],
                ],
            ],
        ];

        abort_unless(isset($definitions[$module]), 404, 'El módulo solicitado no existe.');

        return $definitions[$module];
    }
}
