<?php

namespace App\Http\Requests\Api\V1;

class LegacyMultiCheckinRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_code' => ['required', 'string', 'max:200', 'exists:events,code'],
            'total_records' => ['nullable', 'integer', 'min:0'],
            'data' => ['required', 'array', 'min:1'],
            'data.*.qrcode' => ['required', 'string', 'max:255'],
            'data.*.scan_time' => ['nullable', 'date_format:Y-m-d H:i:s'],
        ];
    }
}
