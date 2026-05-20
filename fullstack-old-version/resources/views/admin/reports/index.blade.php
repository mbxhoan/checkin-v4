
@extends('admin.layouts.templates.page')

@section('title')
    {{ __('reports.index.page.title') }}
@endsection

@section('buttons')

@endsection

@section('primary-content')
    {{-- <div class="my-2">
        <a href=""
            class="btn {{ request()->hasAny([
                'company_id',
                'province_id',
                'status',
                'field_date',
                'from_date',
                'to_date'
            ]) ? 'btn-outline-warning' : 'btn-warning' }}
            btn-sm align-self-center mb-lg-0 mb-2"
            data-bs-toggle="modal"
            data-bs-target="#filterModal"
        >
            {{ __('reports.index.open_filter_button') }}
            <x-icon name="filter"/>
        </a>
        @include('admin.reports._modal-filter', [
            'modalId'       => 'filterModal',
            'title'         => __('reports.index.filter_modal_title'),
            'submitBtn'     => __('reports.index.filter_modal_submit'),
            'model'         => \App\Models\Event::getModel(),
            'route'         => route('admin.reports.index'),
            'companyArray'  => $companyArray,
            'proviceArray'  => $proviceArray,
        ])
    </div> --}}

    <div class="card">
        <div class="card-body">
            <h4 class="card-title">{{ __('reports.index.banner.event_report_list_title') }}</h4>
            <p class="text-xs text-secondary">
                {{ __('reports.index.banner.event_report_list_description') }}
            </p>
            <div class="table-responsive">
                {!! $dataTable->table() !!}
            </div>
        </div>
    </div>
@endsection

@push('admin_js')
    {!! $dataTable->scripts() !!}

    @vite([
        'resources/js/admin/events/index.js'
    ])
@endpush
