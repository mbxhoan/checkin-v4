<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ClientSource;
use App\Enums\ClientStatus;
use Illuminate\Validation\Rule;

class StoreClientRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $eventId = $this->route('event')?->id ?? $this->route('event');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'qrcode' => ['nullable', 'string', 'max:255', Rule::unique('clients', 'qrcode')->where('event_id', $eventId)],
            'status' => ['nullable', Rule::enum(ClientStatus::class)],
            'source' => ['nullable', Rule::enum(ClientSource::class)],
            'custom_fields' => ['nullable', 'array'],
            'registered_at' => ['nullable', 'date'],
            'checked_in_at' => ['nullable', 'date'],
            'checked_out_at' => ['nullable', 'date'],
        ];
    }
}
