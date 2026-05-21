<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\SystemRole;
use Illuminate\Validation\Rule;

class AssignRoleRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::in([
                SystemRole::CompanyAdmin->value,
                SystemRole::CompanyManager->value,
                SystemRole::CompanyUser->value,
                SystemRole::Scanner->value,
            ])],
        ];
    }
}
