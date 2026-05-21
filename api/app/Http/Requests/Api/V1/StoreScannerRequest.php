<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\UserStatus;
use Illuminate\Validation\Rule;

class StoreScannerRequest extends ApiFormRequest
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
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:50'],
            'avatar' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', Rule::enum(UserStatus::class)],
            'device_code' => ['required', 'string', 'max:100', Rule::unique('users', 'device_code')],
            'pin' => ['required', 'string', 'min:4', 'max:10'],
        ];
    }
}
