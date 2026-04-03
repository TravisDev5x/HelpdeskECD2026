<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIncident extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update incident') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
      $rules = [
        'disqualification_date' => 'required',
        'sistema' => 'required',
        'causa' => 'required',
        'responsable' => 'required',
        'acciones' => 'required',
        'observations' => 'required',
        'enablement_date' => 'required',
        'criticidad' => 'required',
        'notas' => 'required',
        'tipo' => 'required',
        'lecciones' => 'nullable'
      ];

      return $rules;
    }
}
