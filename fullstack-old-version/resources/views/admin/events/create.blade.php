@php
    $menuCount = 0;
    $blockCount = 0;
    $features = config("info.events.features");

    if (!auth()->user()->isSysAdmin()) {
        $user = auth()->user();
        $package = $user->package->code;
        $selectedFeatures = config("info.packages.{$package}.events.features") ?? [];
        if (count($selectedFeatures)) $features = array_intersect_key($features, array_flip($selectedFeatures));
    }
@endphp

@extends('admin.layouts.templates.page')

@section('form-action', route('admin.events.store'))
@section('title', __('events.create.title'))

@section('primary-content')
    <div class="row">
        <div class="col-lg-6 col-md-8 mx-auto">
            <form id="{{ $formId ?? null }}" action="@yield('form-action')" class="{{ $formClass ?? "" }}" method="POST" enctype="multipart/form-data">
                @if (!empty($model) && !$model->isNew())
                    @method('PUT')
                @endif
                @csrf
                @php
                    $step2Fields  = ['province_id','type_id','from_date','to_date'];
                    $openStep = session('open_step')
                        ?? old('current_step')
                        ?? (collect($step2Fields)->contains(fn($f) => $errors->has($f)) ? 2 : 1);
                @endphp
                @include('admin/events/_form', [
                    'model'         => $model,
                    'company'       => $company ?? null,
                    'companyArray'  => $companyArray ?? [],
                    'features'      => $features,
                ])
                        {{-- <div class="pull-left my-2">
                            <button type="button" class="btn btn-light d-none" id="btn-prev-step">
                                <i class="fa-solid fa-arrow-left-long me-1"></i> Quay lại
                            </button>
                            @hasSection('form-action')
                                <input type="hidden" name="intent" id="intent" value="{{ $openStep == 1 ? 'save_and_next' : 'save_finish' }}">
                                <button type="submit" class="btn btn-primary {{ $openStep == 1 ? 'd-none' : '' }}" id="btn-submit">
                                <x-icon name="save" />
                                <span>Lưu</span>
                                </button>
                            @endif
                        </div> --}}
            </form>
        </div>
    </div>
@endsection

@push('admin_js')
    @vite([
        'resources/js/admin/events/detail.js'
    ])
@endpush
