
@extends('admin.layouts.templates.page')

@section('title', __('landing_page.manager.title'))

@section('buttons')
    <div class="buttons">
        <a href="{{ route('admin.landing_pages.create') }}" class="btn btn-sm btn-primary align-self-center mb-lg-0 mb-2">
            <x-icon name="plus-square" prefix="fa-regular"/>
            {{ __('landing_page.manager.add_new') }}
        </a>
        {{-- <a href="" class="btn btn-sm btn-primary align-self-center mb-lg-0 mb-2"
            data-bs-toggle="modal"
            data-bs-target="#selectEventModal"
        >
            <x-icon name="plus-square" prefix="fa-regular"/>
            {{ __('landing_page.manager.add_new') }}
        </a>
        <div class="modal fade" id="selectEventModal" data-bs-keyboard="true" tabindex="-1"
            aria-labelledby="selectEventModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="selectEventModalLabel">
                            {{ __('landing_page.manager.select_event_title') }}
                            <a href="{{ route('admin.events.create') }}" class="text-xs text-primary">
                                <x-icon name="plus-square" prefix="fa-regular"/>
                            </a>
                        </h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin.landing_pages.select-event-to-create') }}" method="GET">
                        <div class="modal-body text-sm">
                            <div class="row">
                                <div class="col-md-12">
                                    @include('components.select', [
                                        'fieldName'     => 'event_id',
                                        'id'            => 'event_id',
                                        'options'       => $eventArray,
                                        'selected'      => null,
                                    ])
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                                @lang('common.close')
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary">
                                {{ __('landing_page.manager.select_button') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div> --}}
    </div>
@endsection

@section('primary-content')
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">{{ __('landing_page.manager.list_title') }}</h4>
            <p class="text-xs text-secondary">
                {{ __('landing_page.manager.list_description') }}
            </p>
            <div class="table-responsive">
                {!! $dataTable->table() !!}
            </div>
        </div>
    </div>
@endsection

@push('admin_js')
    {{ $dataTable->scripts() }}
@endpush
