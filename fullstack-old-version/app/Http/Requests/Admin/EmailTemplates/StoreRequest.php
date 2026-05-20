<?php

namespace App\Http\Requests\Admin\EmailTemplates;

use Illuminate\Foundation\Http\FormRequest;

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
            'name' => [
                'required',
                'string',
                'max:200',
            ],
            'subject' => [
                'required',
                'string',
                'max:50',
            ],
        ];
    }

    public function attributes()
    {
        return [
            'name'              => 'Tên template',
            'subject'           => 'Tiêu đề',
        ];
    }
}
