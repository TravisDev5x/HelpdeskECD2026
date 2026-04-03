<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create user') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
      $rules = [
        'name' => 'required',
        'usuario' => [
          'required',
          'unique:users'
        ],
        'phone' => [
          'nullable',
          'max:10',
          'min:10'
        ],
        'email' => [
          'required',
          'email',
          'unique:users'
        ],
        'department_id' => [
          'required',
        ],
        'position_id' => [
          'required',
        ],
        'campaign_id' => [
          'nullable',
          'numeric'
        ],
        'area_id' => [
          'required',
          'numeric'
        ],
        'sedes' => [
          'nullable',
          'array'
        ],
        'sedes.*' => [
          'integer',
          'exists:sedes,id'
        ],
        'password' => [
          'required',
          'confirmed',
          Password::min(8)->mixedCase()->numbers(),
        ]
      ];


      return $rules;
    }
}
