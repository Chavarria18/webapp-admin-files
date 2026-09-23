<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8'],
            'rol' => ['required', Rule::in(Role::assignableBy($this->user())->pluck('name'))],
        ];

        if ($this->input('rol') === 'gerente') {
            $rules['area_ids'] = ['required', 'array', 'min:1'];
            $rules['area_ids.*'] = ['exists:areas,id'];
        } else {
            $rules['area_id'] = ['required', 'exists:areas,id'];
        }

        return $rules;
    }
}
