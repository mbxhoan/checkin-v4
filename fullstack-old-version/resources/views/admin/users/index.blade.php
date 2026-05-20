@extends('admin.layouts.templates.page')

@section('title', __('users.index.page_heading'))

@section('buttons')
    <div class="">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm mb-lg-0 mb-2">
            <x-icon name="plus-square" prefix="fa-regular"/>
            {{ __('users.index.action_create') }}
        </a>
    </div>
@endsection

@section('content')
    <div class="page-header d-lg-flex justify-content-between mb-2">
        <div class="">
            <a href=""
                class="btn {{ request()->hasAny([
                    'company_id',
                    'event_id',
                    'status',
                    'type',
                    'register_source',
                    'field_date',
                    'from_date',
                    'to_date'
                ]) ? 'btn-outline-warning' : 'btn-warning' }}
                btn-sm mb-lg-0 mb-2"
                data-bs-toggle="modal"
                data-bs-target="#filterModal"
            >
                {{ __('users.index.filter_button') }}
                <x-icon name="filter"/>
            </a>
            @include('admin.users._modal-filter', [
                'modalId'       => 'filterModal',
                'title'         => __('users.index.filter_title'),
                'submitBtn'     => __('users.index.filter_submit'),
                'model'         => \App\Models\User::getModel(),
                'route'         => route('admin.users.index'),
                'companyArray'  => $companyArray ?? [],
                'eventArray'    => $eventArray ?? []
            ])
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <h4 class="card-title">{{ __('users.index.card_title') }}</h4>
                {{-- <a href="{{ route('admin.users.refresh') }}" data-turbo-frame="users_list" class="btn btn-xs btn-primary">
                    <x-icon name="refresh" />
                </a> --}}
            </div>
            <p class="text-xs text-secondary">
                {{ __('users.index.card_description') }}
            </p>
            <div class="table-responsive">
                @include('admin/users/_list', [
                    'users' => $users,
                ])
            </div>
        </div>
    </div>
@endsection

@push('admin_js')
    @vite([
        'resources/js/admin/users/index.js'
    ])
@endpush
