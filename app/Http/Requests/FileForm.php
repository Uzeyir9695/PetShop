<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileForm extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'path' => ['required', 'string', 'max:255'],
            'size' => ['required', 'string', 'max:255'],
            'type' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg'],
            ] + ($this->isMethod('POST') ? $this->store() : []);
    }

    protected function store()
    {
        return [
            'uuid' => ['required', 'uuid'],
        ];
    }
}