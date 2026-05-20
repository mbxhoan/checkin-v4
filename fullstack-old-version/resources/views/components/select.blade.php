@if (!empty($label))
    <label class="{{ $labelClass ?? 'form-label' }}" for="{{ $id }}">
        {!! $label !!}
        @if (!empty($required) && $required)
            <span class="text-danger fw-bold fst-italic text-xs alert-required">
                *
            </span>
        @endif
        @if (!empty($unique) && $unique)
            <span class="text-danger fw-bold fst-italic text-xs alert-unique">
                !
            </span>
        @endif
    </label>
@endif

<select
    name="{{ $fieldName }}"
    id="{{ $id }}"
    @class([($formClass ?? 'form-control w-100'), 'is-invalid' => $errors->has($id)])
    {{ !empty($disabled) && $disabled ? "disabled" : "" }}
    {{ !empty($required) && $required ? "required" : "" }}
    data-url="{{ $changeUrl ?? "" }}"
>
    @if (!empty($placeholder))
        <option value="">
            {{ $placeholder }}
        </option>
    @endif
    @foreach ($options as $id => $value)
        <option value="{{ $id }}"
            {{ !empty($selected) && $selected == $id ? "selected" : "" }}
            {{-- @selected(old($fieldName, $model ?? null) == $id) --}}
        >
            {{ $value }}
        </option>
    @endforeach
</select>

@error($id)
    <span class="invalid-feedback">{{ $message }}</span>
@enderror
