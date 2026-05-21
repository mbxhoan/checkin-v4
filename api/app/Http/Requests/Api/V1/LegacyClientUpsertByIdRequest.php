<?php

namespace App\Http\Requests\Api\V1;

class LegacyClientUpsertByIdRequest extends LegacyClientRegisterRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'id' => ['required', 'integer', 'exists:clients,id'],
        ];
    }
}
