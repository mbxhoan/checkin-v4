@extends('admin.layouts.templates.page-form', [
    'bread'     => true,
    'showBtns'  => false
])

@section('form-action', $model->isNew() ? route('admin.companys.store') : route('admin.companys.update', $model))
@section('form-back', route('admin.companys.index'))

@section('title', 'Chi tiết')
@section('li_1', 'Công ty')

@section('primary-content')
    @include('admin/companys/_form', [
        'model'             => $model,
        'settings'          => $settings,
        'currentSettings'   => $currentSettings,
        'templates'         => $templates,
        'senders'           => $senders,
    ])
@endsection

@section('custom-buttons')
    {{-- <div class="footer-fixed d-flex align-items-center justify-content-center">
        <a href="{{ route('admin.companys.index') }}" class="btn btn-sm btn-outline-secondary my-1">
            <x-icon name="chevron-left" />
            @lang('forms.actions.back')
        </a>
        <button id="" type="submit" class="btn btn-sm btn-outline-primary my-1">
            <x-icon name="save" />
            @lang('forms.actions.update')
        </button>
    </div> --}}
    <div class="footer-fixed d-flex align-items-center justify-content-center">
        <button type="button"
                onclick="window.location='{{ route('admin.companys.index') }}'"
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
@endsection

@push('admin_js')
    @vite([
        'resources/js/admin/companys/detail.js'
    ])
@endpush
