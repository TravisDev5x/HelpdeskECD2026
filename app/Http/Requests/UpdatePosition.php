<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePosition extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update position') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
      $rules = [
        'name' => [
          'required',
          'max:255',
          'min:3',
          Rule::unique('positions')->ignore($this->route('position')->id)
        ],
        'area' => 'required|max:255|min:3',
        'extension' => 'nullable|numeric',
      ];

      return $rules;
    }
}
