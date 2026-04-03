<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileUpdateRequest extends FormRequest
{
  /**
  * Determine if the user is authorized to make this request.
  *
  * @return bool
  */
  public function authorize(): bool
  {
    $actor = $this->user();
    $target = $this->route('user');
    if (! $actor || ! $target) {
      return false;
    }
    if ($actor->can('update user')) {
      return true;
    }

    return (int) $actor->id === (int) $target->id;
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
      'email' => [
        'nullable',
        'email',
        Rule::unique('users')->ignore($this->route('user')->id)
      ],
    ];

    if($this->filled('password')) {
      $rules['password'] = ['confirmed', Password::min(8)->mixedCase()->numbers()];
    }

    return $rules;
  }
}
