@extends('admin.layouts.app')

@section('content')
    @if (isset($bread) && $bread)
        @component('components.breadcrumb')
            @slot('title')
                @yield('title')
            @endslot
            @slot('li_1')
                @yield('li_1')
            @endslot
        @endcomponent
    @endif
    <form id="{{ $formId ?? null }}" action="@yield('form-action', '#')" class="{{ $formClass ?? "" }}" method="POST" enctype="multipart/form-data">
        <div class="d-lg-flex justify-content-end">
            <div class="ms-2 text-end">
                @if (isset($showBtns) && !$showBtns)
                    {{-- nothing --}}
                @else
                    <a href="@yield('form-back', '#')" class="btn btn-sm btn-outline-secondary my-1">
                        <x-icon name="chevron-left" />
                        @lang('forms.actions.back')
                    </a>
                    @hasSection('form-action')
                        <button id="{{ $btnSubmitId ?? null }}" type="submit" class="btn btn-sm btn-outline-primary my-1">
                            <x-icon name="save" />
                            @lang('forms.actions.update')
                        </button>
                    @endif
                @endif
                <div class="">
                    @yield('custom-buttons')
                </div>
            </div>
        </div>
        @if (!empty($model) && !$model->isNew())
            @method('PUT')
        @endif
        @csrf
        @yield('primary-content')
    </form>
    @yield('customs')
    @yield('footer')
@endsection
