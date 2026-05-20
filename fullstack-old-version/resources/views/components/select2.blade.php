@if (!empty($label))
    <label class="{{ $labelClass ?? 'form-label' }}" for="{{ $id }}">
        {!! $label !!}
        @if (!empty($required) && $required)
            <span class="text-danger fw-bold fst-italic text-xs alert-required">*</span>
        @endif
        @if (!empty($unique) && $unique)
            <span class="text-danger fw-bold fst-italic text-xs alert-unique">!</span>
        @endif
    </label>
@endif

<select
    name="{{ $fieldName }}"
    id="{{ $id }}"
    @class([($formClass ?? 'form-control w-100 select2'), 'is-invalid' => $errors->has($id)])
    {{ !empty($disabled) && $disabled ? "disabled" : "" }}
    {{ !empty($required) && $required ? "required" : "" }}
    data-placeholder="{{ $placeholder ?? '' }}"
    style="width: 100%;"
>
    @if (!empty($placeholder))
        <option value=""></option>
    @endif
    @foreach ($options as $key => $value)
        <option value="{{ $key }}" {{ isset($selected) && $selected == $key ? 'selected' : '' }}>
            {{ $value }}
        </option>
    @endforeach
</select>

@error($id)
    <span class="invalid-feedback">{{ $message }}</span>
@enderror
@push('script')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%',
                placeholder: function(){
                    return $(this).data('placeholder');
                },
                allowClear: true
            });
        });
    </script>
@endpush
