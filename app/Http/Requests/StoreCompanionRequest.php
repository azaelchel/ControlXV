<?php

namespace App\Http\Requests;

use App\Models\Guest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCompanionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invited_group' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:180'],
            'type' => ['nullable', 'string', 'max:60'],
            'sex' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $group = trim((string) $this->input('invited_group'));

                if ($group === '') {
                    return;
                }

                $exists = Guest::query()
                    ->where('name', $group)
                    ->where('status', 'Confirmado')
                    ->exists();

                if (! $exists) {
                    $validator->errors()->add('invited_group', 'Solo puedes registrar invitados para familias o grupos confirmados.');
                }
            },
        ];
    }
}
