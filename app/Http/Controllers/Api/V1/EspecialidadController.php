<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Especialidad;
use App\Models\Medico;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Especialidades médicas — API pública v1 (actividad 4.2).
 *
 *  GET    /api/v1/especialidades                 Consultar todas
 *  GET    /api/v1/especialidades/{id}            Consultar una
 *  GET    /api/v1/especialidades/{id}/medicos    Médicos de la especialidad (relación)
 *  POST   /api/v1/especialidades                 Registrar
 *  PUT    /api/v1/especialidades/{id}            Reemplazar los datos
 *  PATCH  /api/v1/especialidades/{id}            Actualizar parcialmente
 *  DELETE /api/v1/especialidades/{id}            Eliminar (solo si ningún médico la usa)
 */
class EspecialidadController extends ApiController
{
    private const CAMPOS = ['nombre', 'descripcion'];

    private const ATRIBUTOS = [
        'nombre'      => 'nombre',
        'descripcion' => 'descripción',
    ];

    // GET /api/v1/especialidades
    public function index(): JsonResponse
    {
        $especialidades = Especialidad::withCount('medicos')
            ->orderBy('nombre')
            ->get()
            ->map(fn (Especialidad $e) => $this->presentar($e));

        return $this->exito('Especialidades consultadas correctamente', $especialidades);
    }

    // GET /api/v1/especialidades/{id}
    public function show(string $id): JsonResponse
    {
        if ($error = $this->idInvalido($id)) {
            return $error;
        }

        $especialidad = Especialidad::find($id);

        if (!$especialidad) {
            return $this->noEncontrado('La especialidad solicitada no existe');
        }

        $especialidad->loadCount('medicos');

        return $this->exito('Especialidad consultada correctamente', $this->presentar($especialidad));
    }

    // GET /api/v1/especialidades/{id}/medicos
    public function medicos(string $id): JsonResponse
    {
        if ($error = $this->idInvalido($id)) {
            return $error;
        }

        $especialidad = Especialidad::find($id);

        if (!$especialidad) {
            return $this->noEncontrado('La especialidad solicitada no existe');
        }

        $medicos = Medico::with('usuario')
            ->where('especialidad_id', $especialidad->id_especialidad)
            ->get()
            ->map(fn (Medico $m) => [
                'id'          => $m->id_medico,
                'nombre'      => $m->nombre_completo,
                'cedula'      => $m->cedula,
                'consultorio' => $m->consultorio,
                'activo'      => $m->activo,
            ]);

        return $this->exito("Médicos de la especialidad {$especialidad->nombre} consultados correctamente", $medicos);
    }

    // POST /api/v1/especialidades
    public function store(Request $request): JsonResponse
    {
        $validator = $this->validador($request->all(), $this->reglas('completa'), self::ATRIBUTOS, $this->mensajes());

        if ($validator->fails()) {
            return $this->errorValidacion($validator);
        }

        $especialidad = Especialidad::create($validator->validated());
        $especialidad->loadCount('medicos');

        return $this->exito('Especialidad registrada correctamente', $this->presentar($especialidad), 201);
    }

    // PUT /api/v1/especialidades/{id}
    public function update(Request $request, string $id): JsonResponse
    {
        return $this->actualizar($request, $id, 'completa');
    }

    // PATCH /api/v1/especialidades/{id}
    public function patch(Request $request, string $id): JsonResponse
    {
        return $this->actualizar($request, $id, 'parcial');
    }

    // DELETE /api/v1/especialidades/{id}
    public function destroy(string $id): JsonResponse
    {
        if ($error = $this->idInvalido($id)) {
            return $error;
        }

        $especialidad = Especialidad::find($id);

        if (!$especialidad) {
            return $this->noEncontrado('La especialidad que intentas eliminar no existe');
        }

        // La llave foránea medicos.especialidad_id (onDelete restrict) impide borrarla si está en uso
        $medicos = Medico::where('especialidad_id', $especialidad->id_especialidad)->count();

        if ($medicos > 0) {
            return $this->error(
                'No se puede eliminar la especialidad porque tiene médicos asignados',
                409,
                ['medicos' => ["Hay {$medicos} médico(s) registrados con esta especialidad."]]
            );
        }

        $especialidad->delete();

        return $this->exito('Especialidad eliminada correctamente', ['id' => (int) $id]);
    }

    // ------------------------------------------------------------------

    private function actualizar(Request $request, string $id, string $modo): JsonResponse
    {
        if ($error = $this->idInvalido($id)) {
            return $error;
        }

        $especialidad = Especialidad::find($id);

        if (!$especialidad) {
            return $this->noEncontrado('La especialidad que intentas actualizar no existe');
        }

        if ($modo === 'parcial' && !$request->hasAny(self::CAMPOS)) {
            return $this->sinCamposParaActualizar(self::CAMPOS);
        }

        $validator = $this->validador(
            $request->all(),
            $this->reglas($modo, $especialidad->id_especialidad),
            self::ATRIBUTOS,
            $this->mensajes()
        );

        if ($validator->fails()) {
            return $this->errorValidacion($validator);
        }

        $datos = $validator->validated();

        // En PUT, si no se manda la descripción se reemplaza por null (reemplazo completo)
        if ($modo === 'completa') {
            $datos += ['descripcion' => null];
        }

        $especialidad->update($datos);
        $especialidad->loadCount('medicos');

        return $this->exito('Especialidad actualizada correctamente', $this->presentar($especialidad));
    }

    private function reglas(string $modo, ?int $ignorarId = null): array
    {
        $obligatorio = $modo === 'parcial' ? ['sometimes', 'required'] : ['required'];

        return [
            'nombre' => [
                ...$obligatorio, 'string', 'min:3', 'max:80',
                'regex:/^[\pL\s\.\-]+$/u',
                Rule::unique('especialidades', 'nombre')->ignore($ignorarId, 'id_especialidad'),
            ],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ];
    }

    private function mensajes(): array
    {
        return [
            'nombre.unique' => 'Ya existe una especialidad con ese nombre.',
            'nombre.regex'  => 'El nombre solo puede contener letras, espacios, puntos y guiones.',
        ];
    }

    private function presentar(Especialidad $especialidad): array
    {
        return [
            'id'            => $especialidad->id_especialidad,
            'nombre'        => $especialidad->nombre,
            'descripcion'   => $especialidad->descripcion,
            'total_medicos' => (int) ($especialidad->medicos_count ?? 0),
        ];
    }
}
