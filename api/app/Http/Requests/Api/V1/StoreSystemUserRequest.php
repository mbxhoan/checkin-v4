<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\SystemRole;
use App\Enums\UserStatus;
use Illuminate\Validation\Rule;

class StoreSystemUserRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:50'],
            'avatar' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', Rule::enum(UserStatus::class)],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::in([
                SystemRole::SystemAdmin->value,
                SystemRole::SystemAudit->value,
                SystemRole::SystemSupport->value,
            ])],
        ];
    }
}
