<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProduct extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update product') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
      $rules = [
        'company_id' => 'required',
        'serie' => 'required|max:255',
        'name' => 'required|max:255',
        'etiqueta' => 'required|max:255',
        'marca' => 'nullable|max:255',
        'modelo' => 'nullable|max:255',
        'medio' => 'nullable|max:255',
        'ip' => 'nullable|max:255',
        'mac' => 'nullable|max:255',
        'fecha_ingreso' => 'nullable|date',
        'status' => 'required',
        'costo' => 'numeric',
        'observacion' => 'nullable',
        'sede_id' => 'required',
        'ubicacion_id' => 'required',
      ];

      return $rules;
    }
}
