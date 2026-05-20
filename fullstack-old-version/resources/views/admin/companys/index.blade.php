
@extends('admin.layouts.templates.page', [
    'pageTitle' => __('companys.page_title')
])

@section('title')
    @lang('companys.index_title')
@endsection

@section('buttons')
    <div class="buttons">
        <a href="{{ route('admin.companys.create') }}" class="btn btn-primary btn-sm align-self-center mb-lg-0 mb-2">
            <x-icon name="plus-square" prefix="fa-regular"/>
            @lang('forms.actions.add')
        </a>
    </div>
@endsection

@section('primary-content')
    <div class="table-responsive">
        {!! $dataTable->table() !!}
    </div>
@endsection

@push('admin_js')
    {{ $dataTable->scripts() }}
@endpush
