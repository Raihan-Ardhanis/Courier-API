<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CourierUpdateRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'phone' => [
                'sometimes',
                'required',
                'string',
                'max:30',
                Rule::unique('couriers', 'phone')->ignore($this->route('courier')),
            ],
            'email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255',
                Rule::unique('couriers', 'email')->ignore($this->route('courier')),
            ],
            'address' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'vehicle_type' => ['sometimes', 'nullable', 'string', 'max:80'],
            'level' => ['sometimes', 'required', 'integer', 'between:1,5'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
