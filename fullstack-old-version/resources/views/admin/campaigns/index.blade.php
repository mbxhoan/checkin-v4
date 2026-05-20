
@extends('admin.layouts.templates.page', [
    'pageTitle' => __('campaigns.manage.page_title')
])

@section('title', __('campaigns.manage.title'))

@section('title_')
    <div class="d-flex">
        <div class="bg-light border rounded shadown-sm px-4 py-3">
            <h5>
                {{ __('campaigns.manage.stats_campaigns') }}
            </h5>
            <div class="text-danger">
                {{ $total ?? 0 }}
            </div>
        </div>
        <div class="bg-light border rounded shadown-sm px-4 py-3 ms-2">
            <h5 class="mb-0 pb-0">
                {{ __('campaigns.manage.stats_sent') }}
            </h5>
            <div class="text-danger">
                <span class="text-lg">{{ $sentEmailCount ?? 0 }} </span>
                @if (!empty($limitedEmails))
                    <span class="text-xs text-secondary">/{{ $limitedEmails }}</span>
                    @include('components._progress', [
                        'completed'     => $sentEmailCount ?? 0,
                        'total'         => $limitedEmails ?? $sentEmailCount,
                        'width'         => 300,
                    ])
                @else
                    <span class="text-xs text-secondary">{{ __('campaigns.manage.stats_unlimited') }}</span>
                @endif
            </div>
        </div>
        @if (count($dataStatuses))
            @foreach ($dataStatuses as $status => $count)
                <div class="bg-light border rounded shadown-sm px-4 py-3 ms-2" style="width: 150px;">
                    <h5>
                        {{ $status }}
                    </h5>
                    <div class="text-danger">
                        {{ $count }}
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection

@section('buttons')
    <div class="buttons">
        <a href="{{ route('admin.campaigns.create') }}" class="btn btn-sm btn-primary align-self-center mb-1 ms-1">
            <x-icon name="plus-square" prefix="fa-regular"/>
            {{ __('campaigns.manage.add_new') }}
        </a>
    </div>
@endsection

@section('primary-content')
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">{{ __('campaigns.manage.list_title') }}</h4>
            <p class="text-xs text-secondary">
                {{ __('campaigns.manage.list_description') }}
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
