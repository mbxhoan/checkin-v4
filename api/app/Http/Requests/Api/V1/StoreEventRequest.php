<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\EventStatus;
use Illuminate\Validation\Rule;

class StoreEventRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->route('company')?->id ?? $this->route('company');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100', Rule::unique('events', 'code')->where('company_id', $companyId)],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:500'],
            'venue' => ['nullable', 'string', 'max:500'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', Rule::enum(EventStatus::class)],
            'settings' => ['nullable', 'array'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],
            'timezone' => ['nullable', 'string', 'max:50'],
        ];
    }
}
