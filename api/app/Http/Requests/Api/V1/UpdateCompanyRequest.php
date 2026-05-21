<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\CompanyStatus;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->route('company')?->id ?? $this->route('company');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('companies', 'slug')->ignore($companyId)],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'address' => ['sometimes', 'nullable', 'string'],
            'logo' => ['sometimes', 'nullable', 'string', 'max:500'],
            'status' => ['sometimes', Rule::enum(CompanyStatus::class)],
            'settings' => ['sometimes', 'nullable', 'array'],
            'max_events' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'max_users' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'subscription_plan' => ['sometimes', 'nullable', 'string', 'max:50'],
            'subscription_expires_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
