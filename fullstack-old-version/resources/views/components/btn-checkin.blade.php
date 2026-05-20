@if ($route)
    <form
        action="{{ $route }}"
        method="POST" class="form-inline" data-confirm="{{ $confirm ?? null }}">
        @csrf
        @include('components.form-groups.input-group', [
            'id'                => "event_code",
            'fieldName'         => "event_code",
            'value'             => $model->event_code,
            'type'              => "hidden",
            'formClass'         => 'd-none',
        ])
        @include('components.form-groups.input-group', [
            'id'                => "qrcode",
            'fieldName'         => "qrcode",
            'value'             => $model->qrcode,
            'type'              => "hidden",
            'formClass'         => 'd-none',
        ])
        @if ($model->findCheckin())
            <button type="button" class="btn btn-secondary btn-xs">
                {{-- <x-icon name="check" /> --}}
                {{ $text ?? null }}
            </button>
        @else
            <button type="submit" name="submit" class="{{ $class ?? 'btn btn-success btn-sm' }}">
                {{-- <x-icon name="check" /> --}}
                {{ $text ?? null }}
            </button>
        @endif
    </form>
@endif
