<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Validation\Rule;

class LegacyClientFindRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_id' => ['nullable', 'integer', 'exists:events,id', 'required_without:event_code'],
            'event_code' => ['nullable', 'string', 'exists:events,code', 'required_without:event_id'],
            'qrcode' => ['nullable', 'string', 'max:255', Rule::requiredIf(! $this->filled('id'))],
            'id' => ['nullable', 'integer', 'exists:clients,id', Rule::requiredIf(! $this->filled('qrcode'))],
        ];
    }
}
