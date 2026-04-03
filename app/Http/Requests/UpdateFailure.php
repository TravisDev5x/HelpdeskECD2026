<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFailure extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update failure') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
      $rules = [
        'area_id' => 'required',
        'name' => [
          'required',
          'max:255',
          'min:3',
          Rule::unique('failures')->ignore($this->route('failure')->id)
        ],
      ];

      return $rules;
    }
}
