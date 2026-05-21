<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\UserStatus;
use Illuminate\Validation\Rule;

class UpdateScannerRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('scanner')?->id ?? $this->route('scanner');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'avatar' => ['sometimes', 'nullable', 'string', 'max:500'],
            'status' => ['sometimes', Rule::enum(UserStatus::class)],
            'device_code' => ['sometimes', 'string', 'max:100', Rule::unique('users', 'device_code')->ignore($userId)],
            'pin' => ['sometimes', 'nullable', 'string', 'min:4', 'max:10'],
        ];
    }
}
