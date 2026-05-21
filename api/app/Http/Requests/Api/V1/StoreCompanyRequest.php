<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\CompanyStatus;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('companies', 'slug')],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'logo' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', Rule::enum(CompanyStatus::class)],
            'settings' => ['nullable', 'array'],
            'max_events' => ['nullable', 'integer', 'min:1'],
            'max_users' => ['nullable', 'integer', 'min:1'],
            'subscription_plan' => ['nullable', 'string', 'max:50'],
            'subscription_expires_at' => ['nullable', 'date'],
        ];
    }
}
