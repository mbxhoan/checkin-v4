<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ClientSource;
use App\Enums\ClientStatus;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $eventId = $this->route('event')?->id ?? $this->route('event');
        $clientId = $this->route('client')?->id ?? $this->route('client');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'qrcode' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('clients', 'qrcode')->where('event_id', $eventId)->ignore($clientId)],
            'status' => ['sometimes', Rule::enum(ClientStatus::class)],
            'source' => ['sometimes', Rule::enum(ClientSource::class)],
            'custom_fields' => ['sometimes', 'nullable', 'array'],
            'registered_at' => ['sometimes', 'nullable', 'date'],
            'checked_in_at' => ['sometimes', 'nullable', 'date'],
            'checked_out_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
