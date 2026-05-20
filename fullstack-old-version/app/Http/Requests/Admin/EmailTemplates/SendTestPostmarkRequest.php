<?php

namespace App\Http\Requests\Admin\EmailTemplates;

use Illuminate\Foundation\Http\FormRequest;

class SendTestPostmarkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'from_name' => [
                'required',
                'string',
                'max:200',
            ],
            'from_mail' => [
                'required',
                'email',
                'max:50',
            ],
            'to_mail' => [
                'required',
                'email',
                'max:50',
            ],
            'cc' => [
                'nullable',
                'string',
                'max:200',
                function ($attribute, $value, $fail) {
                    $invalidEmails = $this->getInvalidEmailList($value);
                    if (count($invalidEmails)) {
                        $label = $this->attributes()[$attribute] ?? $attribute;
                        $fail("{$label} không hợp lệ: " . implode(', ', $invalidEmails));
                    }
                },
            ],
            'bcc' => [
                'nullable',
                'string',
                'max:200',
                function ($attribute, $value, $fail) {
                    $invalidEmails = $this->getInvalidEmailList($value);
                    if (count($invalidEmails)) {
                        $label = $this->attributes()[$attribute] ?? $attribute;
                        $fail("{$label} không hợp lệ: " . implode(', ', $invalidEmails));
                    }
                },
            ],
            'fields' => [
                'nullable',
                'array',
            ]
        ];
    }

    public function attributes()
    {
        return [
            'from_name'         => 'Tên người gửi',
            'from_mail'         => 'Email gửi',
            'to_mail'           => 'Email nhận',
            'bcc'               => 'Email bcc',
            'cc'                => 'Email cc',
        ];
    }

    protected function getInvalidEmailList(?string $rawValue): array
    {
        if (empty($rawValue)) {
            return [];
        }

        return collect(explode(',', $rawValue))
            ->map(fn($email) => trim($email))
            ->filter()
            ->reject(fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->values()
            ->all();
    }
}
