<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update user') ?? false;
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
        'ap_paterno' => 'required',
        'ap_materno' => 'required',
        'usuario' => [
          'required',
          Rule::unique('users')->ignore($this->route('user')->id)
        ],
        'email' => [
          'required',
          'email',
          Rule::unique('users')->ignore($this->route('user')->id)
        ],
        'phone' => [
          'nullable',
          'max:10',
          'min:10'
        ],
        'department_id' => [
          'required',
          'numeric'
        ],
        'position_id' => [
          'required',
          'numeric'
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
 
      ];

      if($this->filled('password')) {
        $rules['password'] = ['confirmed', Password::min(8)->mixedCase()->numbers()];
      }

      return $rules;
    }
}
