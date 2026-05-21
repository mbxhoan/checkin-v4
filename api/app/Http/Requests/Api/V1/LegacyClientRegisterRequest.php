<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ClientSource;
use App\Enums\ClientStatus;
use Illuminate\Validation\Rule;

class LegacyClientRegisterRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'exists:clients,id'],
            'event_id' => ['nullable', 'integer', 'exists:events,id', 'required_without:event_code'],
            'event_code' => ['nullable', 'string', 'exists:events,code', 'required_without:event_id'],
            'qrcode' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', Rule::enum(ClientStatus::class)],
            'source' => ['nullable', Rule::enum(ClientSource::class)],
            'custom_fields' => ['nullable', 'array'],
            'registered_at' => ['nullable', 'date'],
            'checked_in_at' => ['nullable', 'date'],
            'checked_out_at' => ['nullable', 'date'],
            'type' => ['nullable', 'string', 'max:50'],
            'lang' => ['nullable', 'string', 'max:20'],
            'ref_id' => ['nullable', 'integer'],
            'campaign_id' => ['nullable', 'integer'],
            'card_id' => ['nullable', 'integer'],
            'slug' => ['nullable', 'string', 'max:255'],
        ];
    }
}
