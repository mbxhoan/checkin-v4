<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\CheckinType;
use Illuminate\Validation\Rule;

class ListCheckinsRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['nullable', Rule::enum(CheckinType::class)],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'qrcode' => ['nullable', 'string', 'max:255'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
