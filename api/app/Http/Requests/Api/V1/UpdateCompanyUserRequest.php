<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\SystemRole;
use App\Enums\UserStatus;
use Illuminate\Validation\Rule;

class UpdateCompanyUserRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['sometimes', 'string', 'min:8'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'avatar' => ['sometimes', 'nullable', 'string', 'max:500'],
            'status' => ['sometimes', Rule::enum(UserStatus::class)],
            'roles' => ['sometimes', 'array', 'min:1'],
            'roles.*' => ['string', Rule::in([
                SystemRole::CompanyAdmin->value,
                SystemRole::CompanyManager->value,
                SystemRole::CompanyUser->value,
                SystemRole::Scanner->value,
            ])],
        ];
    }
}
