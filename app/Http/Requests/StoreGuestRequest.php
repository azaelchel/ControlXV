<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreGuestRequest extends FormRequest
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
            'group_name' => ['required', 'string', 'max:120'],
            'prefix' => ['nullable', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', 'max:40'],
            'status' => ['required', 'string', 'max:60'],
            'phone' => ['nullable', 'string', 'max:40'],
            'adults' => ['required', 'integer', 'min:0', 'max:999'],
            'adolescents' => ['required', 'integer', 'min:0', 'max:999'],
            'children' => ['required', 'integer', 'min:0', 'max:999'],
            'sponsor' => ['nullable', 'string', 'max:120'],
            'whatsapp_2_months' => ['nullable', 'string', 'max:40'],
            'whatsapp_1_month' => ['nullable', 'string', 'max:40'],
            'whatsapp_15_days' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $phone = $this->input('phone');

        $this->merge([
            'phone' => is_string($phone) ? preg_replace('/\D+/', '', $phone) : $phone,
        ]);
    }
}
