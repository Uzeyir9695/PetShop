<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'phone_number' => ['required', 'string', 'max:255'],
                'avatar' => ['nullable', 'string', 'max:255'],
                'address' => ['required', 'string', 'max:255'],
                'is_admin' => ['nullable', 'boolean'],
                'is_marketing' => ['nullable', 'boolean'],
            ] + ($this->isMethod('POST') ? $this->store() : []);
    }

    protected function store()
    {
        return [
            'uuid' => 'required|uuid',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ];
    }

}
