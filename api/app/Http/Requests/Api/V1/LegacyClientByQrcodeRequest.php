<?php

namespace App\Http\Requests\Api\V1;

class LegacyClientByQrcodeRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'qrcode' => ['required', 'string', 'max:255'],
        ];
    }
}
