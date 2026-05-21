<?php

namespace App\Http\Requests\Api\V1;

class LegacyCheckinRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_code' => ['required', 'string', 'max:200', 'exists:events,code'],
            'qrcode' => ['required', 'string', 'max:255'],
            'scan_time' => ['nullable', 'date_format:Y-m-d H:i:s'],
        ];
    }
}
