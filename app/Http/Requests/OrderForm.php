<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderForm extends FormRequest
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
            'products' => ['required', 'json'],
            'address' => ['required', 'json'],
            'delivery_fee' => ['nullable', 'regex:/^\d+(\.\d{1,2})?$/'],
            'amount' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'shipped_at' => ['nullable', 'date'],
            ] + ($this->isMethod('POST') ? $this->store() : []);
    }

    protected function store()
    {
        return [
            'uuid' => ['required', 'uuid'],
            'user_id' => ['required'],
            'order_status_id' => ['required'],
            'payment_id' => ['required'],
        ];
    }
}
