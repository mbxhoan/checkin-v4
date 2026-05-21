<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\EventStatus;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->route('company')?->id ?? $this->route('company');
        $eventId = $this->route('event')?->id ?? $this->route('event');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'nullable', 'string', 'max:100', Rule::unique('events', 'code')->where('company_id', $companyId)->ignore($eventId)],
            'description' => ['sometimes', 'nullable', 'string'],
            'location' => ['sometimes', 'nullable', 'string', 'max:500'],
            'venue' => ['sometimes', 'nullable', 'string', 'max:500'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date'],
            'status' => ['sometimes', Rule::enum(EventStatus::class)],
            'settings' => ['sometimes', 'nullable', 'array'],
            'max_attendees' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'timezone' => ['sometimes', 'nullable', 'string', 'max:50'],
        ];
    }
}
