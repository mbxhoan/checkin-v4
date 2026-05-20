@extends('admin.layouts.app')

@section('content')
    <div class="page-header d-lg-flex justify-content-between">
        @if ($pageTitle)
            <h3>{{ $pageTitle }}</h3>
        @endif
        @yield('buttons')
    </div>
    <div class="container-fluid px-0">
        <div class="row">
            <div class="{{ $colLeft ?? "col-md-6" }}">
                @yield('sub_title')
                <div class="{{ $divClass ?? "" }}">
                    @yield('primary-content')
                </div>
                <div class="mt-lg-4 mt-3">
                    @yield('table')
                </div>
                <div class="mt-lg-4 mt-3">
                    @yield('customs')
                </div>
            </div>
            <div class="{{ $colRight ?? "col-md-6" }}">
                @yield('secondary-content')
            </div>
        </div>
    </div>
@endsection
