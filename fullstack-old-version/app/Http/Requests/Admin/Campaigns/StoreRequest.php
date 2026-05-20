<?php

namespace App\Http\Requests\Admin\Campaigns;

use App\Models\Campaign;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
            'event_id' => [
                'required',
                'integer',
                'exists:events,id',
            ],
            'template_id' => [
                'required',
                'integer',
            ],
            'name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'type' => [
                'nullable',
                'string',
                'max:50',
            ],
            'subject' => [
                'nullable',
                'string',
                'max:50',
            ],
            'from_email' => [
                'required',
                'email',
                'max:50',
                // Rule::in([
                //     'admin@delfi.vn'
                // ]),
            ],
            'from_name' => [
                'nullable',
                'string',
                'max:50',
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in(array_keys(Campaign::STATUES)),
            ],
            'cc' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $invalidEmails = $this->getInvalidEmailList($value);
                    if (count($invalidEmails)) {
                        $label = $this->attributes()[$attribute] ?? $attribute;
                        $fail(__('campaigns.validation.invalid_email_list', [
                            'attribute' => $label,
                            'emails' => implode(', ', $invalidEmails),
                        ]));
                    }
                },
            ],
            'bcc' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $invalidEmails = $this->getInvalidEmailList($value);
                    if (count($invalidEmails)) {
                        $label = $this->attributes()[$attribute] ?? $attribute;
                        $fail(__('campaigns.validation.invalid_email_list', [
                            'attribute' => $label,
                            'emails' => implode(', ', $invalidEmails),
                        ]));
                    }
                },
            ],
            'message_stream' => [
                'nullable',
                'string',
                'max:50',
            ],
            'limitation_per_time' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'hold_time' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'scheduled_at' => [
                'nullable',
                'date',
                'after_or_equal:now',
            ],
            'fixed_attachments' => [
                'nullable',
                'array',
            ],
        ];
    }

    public function messages()
    {
        return [
            'scheduled_at.after_or_equal' => __('campaigns.validation.scheduled_at_not_past'),
        ];
    }

    public function attributes()
    {
        return [
            'event_id' => __('campaigns.validation.attributes.event_id'),
            'template_id' => __('campaigns.validation.attributes.template_id'),
            'name' => __('campaigns.validation.attributes.name'),
            'type' => __('campaigns.validation.attributes.type'),
            'subject' => __('campaigns.validation.attributes.subject'),
            'from_email' => __('campaigns.validation.attributes.from_email'),
            'from_name' => __('campaigns.validation.attributes.from_name'),
            'cc' => __('campaigns.validation.attributes.cc'),
            'bcc' => __('campaigns.validation.attributes.bcc'),
            'message_stream' => __('campaigns.validation.attributes.message_stream'),
            'limitation_per_time' => __('campaigns.validation.attributes.limitation_per_time'),
            'hold_time' => __('campaigns.validation.attributes.hold_time'),
            'scheduled_at' => __('campaigns.validation.attributes.scheduled_at'),
            'fixed_attachments' => __('campaigns.validation.attributes.fixed_attachments'),
        ];
    }

    protected function getInvalidEmailList(?string $rawValue): array
    {
        if (empty($rawValue)) {
            return [];
        }

        return collect(explode(',', $rawValue))
            ->map(fn ($email) => trim($email))
            ->filter()
            ->reject(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->values()
            ->all();
    }
}
