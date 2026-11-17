<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CourierStoreRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30', 'unique:couriers,phone'],
            'email' => ['nullable', 'email', 'max:255', 'unique:couriers,email'],
            'address' => ['nullable', 'string', 'max:1000'],
            'vehicle_type' => ['nullable', 'string', 'max:80'],
            'plate_number' => ['nullable', 'string', 'max:20', 'unique:couriers,plate_number'],
            'level' => ['required', 'integer', 'between:1,5'],
            'status' => ['sometimes', 'in:aktif,nonaktif'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated($key, $default);

        if (is_array($validated)) {
            if (isset($validated['status']) && !isset($validated['is_active'])) {
                $validated['is_active'] = $validated['status'] === 'aktif';
            }
            unset($validated['status']);
        }

        return $validated;
    }
}
