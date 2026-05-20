<?php

namespace App\Http\Requests\Web\Users;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'company_name' => [
                'required',
                'string',
                'max:50',
                'unique:companys,name',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_])[A-Za-z\d\W_]{8,}$/',
                'not_regex:/[À-ỹà-ỹ]/u', // block Vietnamese characters (accents)
            ],
            'phone' => [
                'required',
                'regex:/^[0-9+\-\s_.()]{9,15}$/',
                'unique:'.User::class,
            ],
            'package' => [
                'required',
                'string',
                'max:50',
                'exists:packages,code',
                // Rule::in(array_keys(config('info.packages')))
            ],
            'position' => [
                'nullable',
                'string',
                'max:200',
            ],
            'company_type' => [
                'nullable',
                'string',
                'max:50',
            ],
            'devices' => [
                'nullable',
                'array',
            ],
            'devices.*' => [
                'string',
                Rule::in(array_keys(config('info.devices'))),
            ],
            'accept_terms' => [
                'accepted',
            ],
        ];
    }

    public function attributes()
    {
        return [
            'company_name' => __('validation.attributes.company_name'),
            'name' => __('validation.attributes.name'),
            'password' => __('validation.attributes.password'),
            'password_confirmation' => __('validation.attributes.password_confirmation'),
            'package' => __('validation.attributes.package'),
            'position' => __('validation.attributes.position'),
            'company_type' => __('validation.attributes.company_type'),
            'devices' => __('validation.attributes.devices'),
            'accept_terms' => __('validation.attributes.accept_terms'),
        ];
    }

    public function messages()
    {
        return [
            'password.regex' => __('register.validation.password_regex'),
            'password.not_regex' => __('register.validation.password_not_regex'),
            'accept_terms.accepted' => __('register.validation.accept_terms_accepted'),
        ];
    }
}
