@php
    $menuCount = 0;
    $blockCount = 0;
@endphp

@extends('admin.layouts.templates.page')

@section('form-action', route('admin.events.update', $model))
@section('form-back', route('admin.events.index'))
@section('title', 'Sự kiện')

@section('primary-content')
    <div class="w-100 mb-2">
        <ul class="nav nav-tabs w-100">
            @foreach (config('info.events.details') as $key => $title)
                <li class="nav-item">
                    <a class="nav-link text-xs text-decoration-none text-dark {{ empty(request()->key) ? ($key == "tong-quan" ? "active fw-bold" : "") : (request()->key == $key ? "active fw-bold" : "") }}" aria-current="page"
                        href="{{ route('admin.events.edit', [
                            'event' => $event,
                            'key'   => $key,
                        ]) }}"
                    >
                        {{ $title }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
    @switch(request()->key)
        @case('nguoi-tham-du')
            @include('admin.events.keys._clients', [
                'event' => $model,
            ])
            @break
        @case('settings')
            @include('admin.events.keys._settings', [
                'event' => $model,
            ])
            @break
        @case('checkin')
            @include('admin.events.keys._checkin', [
                'event' => $model,
            ])
            @push('admin_js')
                @vite([
                    'resources/js/admin/checkins/config.js'
                ])
            @endpush
            @break
        @case('settings')
            @include('admin.events.keys._settings', [
                'event' => $model,
            ])
            @break
        @case('them')
            @sys_admin
                <form action="{{ route('admin.events.erase', $event) }}"
                    method="POST"
                    onsubmit="return confirm('Hành động này sẽ không thể khôi phục, bạn có chắc chứ?');"
                >
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i>
                        Xoá dữ liệu
                    </button>
                </form>
            @endsys_admin
            @break
        @default
            <div class="row">
                <div class="col-lg-6 col-md-8 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <form id="{{ $formId ?? null }}" action="@yield('form-action')" class="{{ $formClass ?? "" }}" method="POST" enctype="multipart/form-data">
                                @if (!empty($model) && !$model->isNew())
                                    @method('PUT')
                                @endif
                                @csrf
                                @include('admin/events/_summary', [
                                    'model'         => $model,
                                    'company'       => $company ?? null,
                                    'companyArray'  => $companyArray ?? [],
                                ])
                                {{-- <div class="footer-fixed d-flex align-items-center justify-content-center">
                                    <a href="@yield('form-back')" class="btn btn-light">
                                        <x-icon name="chevron-left" />

                                        @lang('forms.actions.back')
                                    </a>
                                    @hasSection('form-action')
                                        <button type="submit" class="btn btn-primary">
                                            <x-icon name="save" />
                                            @lang('forms.actions.update')
                                        </button>
                                    @endif
                                </div> --}}
                                <div class="footer-fixed d-flex align-items-center justify-content-center">
                                    <button type="button"
                                            onclick="window.location='{{ route('admin.events.index') }}'"
                                            class="btn btn-outline-secondary me-2 d-inline-flex align-items-center">
                                        <x-icon name="chevron-left" />
                                        @lang('forms.actions.back')
                                    </button>

                                    <button type="submit"
                                            class="btn btn-outline-primary d-inline-flex align-items-center">
                                        <x-icon name="save" />
                                        @lang('forms.actions.update')
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
    @endswitch
@endsection

@push('admin_js')
    @vite([
        'resources/js/admin/events/detail.js',
        'resources/js/admin/checkins/config.js'
    ])
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.min.js"></script>
@endpush
