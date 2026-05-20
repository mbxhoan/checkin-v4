
@extends('admin.layouts.templates.page', [
    'pageTitle' => __('labels.index.page.title')
])

@section('title', __('labels.index.table.title'))

@section('buttons')
    <div class="buttons">
        <a href="{{ route('admin.labels.create') }}" class="btn btn-sm btn-primary align-self-center mb-lg-0 mb-2">
            <x-icon name="plus-square" prefix="fa-regular"/>
            {{ __('labels.index.action.create_button') }}
        </a>
    </div>
@endsection

@section('primary-content')
    <x-card>
        <h4 class="card-title">{{ __('labels.index.table.title') }}</h4>
        <p class="text-xs text-secondary">
            {{ __('labels.index.table.description') }}
        </p>
        <div class="table-responsive">
            {!! $dataTable->table() !!}
        </div>
    </x-card>
@endsection

@push('admin_js')
    {{ $dataTable->scripts() }}
@endpush
