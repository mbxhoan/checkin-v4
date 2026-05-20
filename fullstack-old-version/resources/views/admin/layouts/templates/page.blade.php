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
    @yield('primary-content')
@endsection
