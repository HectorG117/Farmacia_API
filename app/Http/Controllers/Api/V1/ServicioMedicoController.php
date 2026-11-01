<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ServicioMedico;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Servicios médicos — API pública v1 (actividad 4.2).
 *
 *  GET    /api/v1/servicios-medicos          Consultar todos (?activo=1|0)
 *  GET    /api/v1/servicios-medicos/{id}     Consultar uno
 *  POST   /api/v1/servicios-medicos          Registrar
 *  PUT    /api/v1/servicios-medicos/{id}     Reemplazar los datos
 *  PATCH  /api/v1/servicios-medicos/{id}     Actualizar parcialmente (por ejemplo solo "activo")
 *  DELETE /api/v1/servicios-medicos/{id}     Eliminar
 */
class ServicioMedicoController extends ApiController
{
    private const CAMPOS = ['nombre', 'descripcion', 'costo', 'activo'];

    private const ATRIBUTOS = [
        'nombre'      => 'nombre',
        'descripcion' => 'descripción',
        'costo'       => 'costo',
        'activo'      => 'activo',
    ];

    // GET /api/v1/servicios-medicos
    public function index(Request $request): JsonResponse
    {
        $validator = $this->validador($request->query(), ['activo' => 'nullable|in:0,1,true,false'], self::ATRIBUTOS);

        if ($validator->fails()) {
            return $this->errorValidacion($validator);
        }

        $servicios = ServicioMedico::query()
            ->when($request->filled('activo'), fn ($q) => $q->where('activo', $request->boolean('activo')))
            ->orderBy('nombre')
            ->get()
            ->map(fn (ServicioMedico $s) => $this->presentar($s));

        return $this->exito('Servicios médicos consultados correctamente', $servicios);
    }

    // GET /api/v1/servicios-medicos/{id}
    public function show(string $id): JsonResponse
    {
        if ($error = $this->idInvalido($id)) {
            return $error;
        }

        $servicio = ServicioMedico::find($id);

        if (!$servicio) {
            return $this->noEncontrado('El servicio médico solicitado no existe');
        }

        return $this->exito('Servicio médico consultado correctamente', $this->presentar($servicio));
    }

    // POST /api/v1/servicios-medicos
    public function store(Request $request): JsonResponse
    {
        $validator = $this->validador($request->all(), $this->reglas('completa'), self::ATRIBUTOS);

        if ($validator->fails()) {
            return $this->errorValidacion($validator);
        }

        $datos = $validator->validated();
        $datos['activo'] = $request->has('activo') ? $request->boolean('activo') : true;

        $servicio = ServicioMedico::create($datos);

        return $this->exito('Servicio médico registrado correctamente', $this->presentar($servicio->fresh()), 201);
    }

    // PUT /api/v1/servicios-medicos/{id}
    public function update(Request $request, string $id): JsonResponse
    {
        return $this->actualizar($request, $id, 'completa');
    }

    // PATCH /api/v1/servicios-medicos/{id}
    public function patch(Request $request, string $id): JsonResponse
    {
        return $this->actualizar($request, $id, 'parcial');
    }

    // DELETE /api/v1/servicios-medicos/{id}
    public function destroy(string $id): JsonResponse
    {
        if ($error = $this->idInvalido($id)) {
            return $error;
        }

        $servicio = ServicioMedico::find($id);

        if (!$servicio) {
            return $this->noEncontrado('El servicio médico que intentas eliminar no existe');
        }

        $servicio->delete();

        return $this->exito('Servicio médico eliminado correctamente', ['id' => (int) $id]);
    }

    // ------------------------------------------------------------------

    private function actualizar(Request $request, string $id, string $modo): JsonResponse
    {
        if ($error = $this->idInvalido($id)) {
            return $error;
        }

        $servicio = ServicioMedico::find($id);

        if (!$servicio) {
            return $this->noEncontrado('El servicio médico que intentas actualizar no existe');
        }

        if ($modo === 'parcial' && !$request->hasAny(self::CAMPOS)) {
            return $this->sinCamposParaActualizar(self::CAMPOS);
        }

        $validator = $this->validador($request->all(), $this->reglas($modo), self::ATRIBUTOS);

        if ($validator->fails()) {
            return $this->errorValidacion($validator);
        }

        $datos = $validator->validated();

        if ($request->has('activo')) {
            $datos['activo'] = $request->boolean('activo');
        }

        // En PUT, los campos opcionales que no se envían se reemplazan por su valor por defecto
        if ($modo === 'completa') {
            $datos += ['descripcion' => null, 'activo' => true];
        }

        $servicio->update($datos);

        return $this->exito('Servicio médico actualizado correctamente', $this->presentar($servicio->fresh()));
    }

    private function reglas(string $modo): array
    {
        $obligatorio = $modo === 'parcial' ? 'sometimes|required' : 'required';

        return [
            'nombre'      => "{$obligatorio}|string|min:3|max:100",
            'descripcion' => 'nullable|string|max:1000',
            'costo'       => "{$obligatorio}|numeric|decimal:0,2|min:0|max:99999999.99",
            'activo'      => 'sometimes|boolean',
        ];
    }

    private function presentar(ServicioMedico $servicio): array
    {
        return [
            'id'          => $servicio->id_servicio,
            'nombre'      => $servicio->nombre,
            'descripcion' => $servicio->descripcion,
            'costo'       => (float) $servicio->costo,
            'activo'      => (bool) $servicio->activo,
        ];
    }
}
