
@extends('admin.layouts.templates.page', [
    'pageTitle' => __('cards.index.page_title')
])

@section('title', __('cards.index.page_heading'))

@section('buttons')
    <div class="buttons">
        <a href="{{ route('admin.cards.create') }}" class="btn btn-sm btn-primary align-self-center mb-lg-0 mb-2">
            <x-icon name="plus-square" prefix="fa-regular"/>
            {{ __('cards.index.action_create') }}
        </a>
    </div>
@endsection

@section('primary-content')
    <div class="mb-2 d-lg-flex justify-content-between">
        <div class=""></div>
    </div>
    {{ $dataTable->table() }}
@endsection

@push('admin_js')
    {{ $dataTable->scripts() }}
@endpush
