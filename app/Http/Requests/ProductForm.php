<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductForm extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'price' => ['required', 'regex:/^\d+(\.\d{1,2})?$/', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'metadata' => ['required', 'json'],
        ] + ($this->isMethod('POST') ? $this->store() : []);
    }

    protected function store()
    {
        return [
            'uuid' => ['required', 'uuid'],
            'category_uuid' => ['required', 'uuid']
        ];
    }
}
