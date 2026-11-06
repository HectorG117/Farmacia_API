<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AdminResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $parcial = $this->isMethod('PATCH');
        $obligatorio = $parcial ? ['sometimes', 'required'] : ['required'];
        $imagen = $parcial
            ? ['sometimes', 'required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048', 'dimensions:max_width=4000,max_height=4000']
            : ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048', 'dimensions:max_width=4000,max_height=4000'];

        return [
            'nombre' => [...$obligatorio, 'string', 'min:3', 'max:100'],
            'precio' => [...$obligatorio, 'numeric', 'decimal:0,2', 'min:0.01', 'max:999999.99'],
            'stock' => [...$obligatorio, 'integer', 'min:0', 'max:1000000'],
            'categoria' => [...$obligatorio, 'string', 'min:3', 'max:50'],
            'imagen' => $imagen,
        ];
    }

    public function withValidator(Validator $validator): void
    {
        if ($this->isMethod('PATCH')) {
            $validator->after(function (Validator $validator): void {
                if (! $this->hasAny(['nombre', 'precio', 'stock', 'categoria', 'imagen']) && ! $this->hasFile('imagen')) {
                    $validator->errors()->add('campos', 'Envía al menos un campo para actualizar.');
                }
            });
        }
    }
}
