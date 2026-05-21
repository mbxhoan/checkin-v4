<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\CheckinType;
use Illuminate\Validation\Rule;

class ScanCheckinRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'qrcode' => ['required', 'string', 'max:255'],
            'type' => ['nullable', Rule::enum(CheckinType::class)],
            'scanned_at' => ['nullable', 'date'],
            'device_info' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
