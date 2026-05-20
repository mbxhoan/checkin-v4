@php
    $event = \App\Models\Event::getModel();
@endphp

@extends('admin.layouts.templates.page', [
    'pageTitle' => __('events.index.page_title'),
])

@section('title')
    {{ __('events.index.title') }}
@endsection

@section('buttons')
    <div class="buttons">
        @admin
            <a href="{{ route('admin.events.create') }}" class="btn btn-primary btn-sm align-self-center mb-lg-0 mb-2">
                <x-icon name="plus-square" prefix="fa-regular"/>
                @lang('forms.actions.add')
            </a>
        @endadmin
    </div>
@endsection

@section('primary-content')
    <div class="mb-2">
        <div class="d-flex">
            @foreach ($event->getStatues() as $key => $status)
                @php
                    $params = request()->all();
                    $statuses = (array) ($params['statuses'] ?? []);

                    if (in_array($key, $statuses)) {
                        $statuses = array_diff($statuses, [$key]);
                    } else {
                        $statuses[] = $key;
                    }

                    $params['statuses'] = $statuses;
                @endphp
                <a href="{{ route('admin.events.index', $params) }}"
                    class="btn btn-xs align-self-center mb-lg-0 mb-2 me-1
                    {{ in_array($key, (array) request()->input('statuses', [])) ? 'btn-primary' : 'btn-outline-primary' }}"
                >
                    {{ $status }}
                </a>
            @endforeach
            <form
                method="GET"
                action="{{ route('admin.events.index', [
                    'statues' => request()->input('statuses', [])
                ]) }}"
                class="ms-1 d-flex align-items-center gap-2"
            >
                <input
                    type="text"
                    name="from_date"
                    value="{{ request()->has('from_date') ? request('from_date') : '' }}"
                    placeholder="{{ __('events.index.filters.from_date') }}"
                    onfocus="(this.type='date')"
                    onblur="if(!this.value)this.type='text'"
                    class="form-control form-control-sm me-1"
                    style="width: 130px;"
                >
                <input
                    type="text"
                    name="to_date"
                    value="{{ request()->has('to_date') ? request('to_date') : '' }}"
                    placeholder="{{ __('events.index.filters.to_date') }}"
                    onfocus="(this.type='date')"
                    onblur="if(!this.value)this.type='text'"
                    class="form-control form-control-sm me-1"
                    style="width: 130px;"
                >
                @sys_admin
                    <div style="max-width: 150px;">
                        @include('components.select', [
                            'formClass' => 'form-control form-control-sm me-1',
                            'fieldName' => 'company_id',
                            'id' => 'company_id',
                            'options' => ['' => __('events.index.filters.company_option')] + $companyArray,
                            'selected' => request()->has('company_id') ? request('company_id') : null,
                        ])
                    </div>
                @endsys_admin
                <div class="d-inline" style="max-width: 150px;">
                    @include('components.select', [
                        'formClass' => 'form-control form-control-sm me-1',
                        'fieldName' => 'province_id',
                        'id' => 'province_id',
                        'options' => ['' => __('events.index.filters.province_option')] + $proviceArray,
                        'selected' => request()->has('province_id') ? request('province_id') : null,
                    ])
                </div>
                @if (count(request()->all()))
                    <a href="{{ route('admin.events.index') }}" class="btn btn-xs btn-outline-danger">
                        {{ __('events.index.filters.clear') }}
                    </a>
                @endif
                <button type="submit" class="btn btn-xs btn-{{ request()->hasAny([
                    'event_id',
                    'status',
                    'statuses',
                    'type',
                    'register_source',
                    'field_date',
                    'from_date',
                    'to_date'
                ]) ? 'primary' : 'outline-primary' }}">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    {{ __('events.index.filters.search') }}
                </button>
            </form>
        </div>
    </div>
    <div class="row g-2">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">{{ __('events.index.list_title') }}</h4>
                    <p class="text-xs text-secondary">
                        {{ __('events.index.list_description') }}
                    </p>
                    <div class="table-responsive">
                        {!! $dataTable->table() !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <x-card>
                <h4 class="card-title">
                    {{ __('events.index.schedule_title') }}
                </h4>
                <p class="text-xs text-secondary">
                    {{ __('events.index.schedule_description') }}
                </p>
                @if (empty($events) || !$events->count())
                    <div class="alert alert-info text-xs">
                        {{ __('events.index.upcoming_empty') }}
                    </div>
                @else
                    @foreach ($events as $date => $dayEvents)
                        @php
                            $prefix = \Carbon\Carbon::parse($date)->isToday()
                                ? __('events.index.today_prefix')
                                : __('events.index.upcoming_prefix');
                            $date = \Carbon\Carbon::parse($date);
                        @endphp

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="text-right">
                                    <div class="text-xs fw-bold">{{ $prefix }} - {{ $date->format('d-m-Y') }}</div>
                                    <div class="text-sm text-secondary">{{ $date->format('D') }}</div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="flex-1 space-y-4">
                                    @foreach ($dayEvents as $event)
                                        <div class="relative flex items-start bg-white border p-2 rounded">
                                            <div class="absolute -left-8 top-6 w-3 h-3 bg-gray-300 rounded-full"></div>
                                            <div class="row align-items-center">
                                                <div class="col-md-8">
                                                    <p class="font-semibold text-sm fw-bold truncate">
                                                        {{ $event->name }}
                                                    </p>
                                                    <div class="flex items-center text-secondary text-xs">
                                                        📍 {{ $event->province->name }}
                                                    </div>
                                                    <div class="flex items-center text-secondary text-xs">
                                                        👥 {{ __('events.index.guests_count', ['count' => $event->clients->count()]) }}
                                                    </div>
                                                    <div class="mt-2">
                                                        <a href="{{ route('admin.events.edit', $event) }}"
                                                        class="inline-flex items-center text-xs bg-gray-100 rounded hover:bg-gray-200">
                                                            {{ __('events.index.manage_event') }} →
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 flex-shrink-0">
                                                    <img width="100%" src="{{ $event->logoUrl ? $event->logoUrl->getUrl() : asset(config('info.placeholders.image')) }}"
                                                        alt="{{ $event->code }}"
                                                        class="object-cover rounded-md"
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </x-card>
        </div>
    </div>
@endsection

@push('admin_js')
    {!! $dataTable->scripts() !!}
    @vite([
        'resources/js/admin/events/index.js'
    ])
@endpush
