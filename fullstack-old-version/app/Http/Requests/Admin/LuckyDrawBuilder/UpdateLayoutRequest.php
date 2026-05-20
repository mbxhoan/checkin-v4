<?php

namespace App\Http\Requests\Admin\LuckyDrawBuilder;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'canvas_width' => 'nullable|integer|min:800|max:3840',
            'canvas_height' => 'nullable|integer|min:600|max:2160',
            'background_type' => 'nullable|in:color,image,video',
            'background_value' => 'nullable|string|max:2048',
            'blocks' => 'sometimes|array',
            'blocks.*.id' => 'required|string',
            'blocks.*.type' => 'required|in:text,image,avatar,random_field,result_field',
            'blocks.*.x' => 'required|numeric',
            'blocks.*.y' => 'required|numeric',
            'blocks.*.width' => 'nullable|numeric|min:1',
            'blocks.*.height' => 'nullable|numeric|min:1',
            'blocks.*.source' => 'nullable|string',
            'blocks.*.style' => 'nullable|array',
            'blocks.*.animation' => 'nullable|array',
            'blocks.*.visibleWhen' => 'nullable|in:always,spinning,slowing,result',
            'blocks.*.slotIndex' => 'nullable|integer|min:0',
            'blocks.*.rotation' => 'nullable|numeric',
            'blocks.*.zIndex' => 'nullable|integer',
            'blocks.*.locked' => 'nullable|boolean',
            'settings' => 'nullable|array',
        ];
    }
}
