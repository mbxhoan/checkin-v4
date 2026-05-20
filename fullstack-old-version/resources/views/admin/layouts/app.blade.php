<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <title> @yield('title') | {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/brand/favicon.png') }}">

    @include('layouts.head-css')
    @include('components.metadata', [
        'title'         => config("metapage.title"),
        'description'   => config("metapage.description"),
        'robots'        => config("metapage.robots"),
        'url'           => url()->current(),
        'image'         => config("metapage.image"),
        'language'      => app()->getLocale(),
    ])
    @vite([
        'resources/sass/app.scss',
        'resources/sass/admin.scss',
        'resources/js/app.js',
        'resources/js/admin.js'
    ])
    @stack('admin_css')
    @livewireStyles
</head>

@section('body')
    <body data-sidebar="dark" data-layout-mode="light">
@show
    <!-- Begin page -->
    <div id="layout-wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @include('shared/alerts')
                    @yield('content')
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->
            @include('layouts.footer')
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <!-- Right Sidebar -->
    @include('layouts.right-sidebar')
    <!-- /Right-bar -->

    @stack('modals')

    <!-- JAVASCRIPT -->
    @include('layouts.vendor-scripts')
    @include('components.direct-print-config')
    @if (config('services.n8n_chatbot.enabled', true))
        @include('admin.components.n8n-chatbot-widget')
    @endif

    @if (session('success'))
        <script>
            toastr.success(@json(session('success')), @json(__('common.toast.success_title')));
        </script>
    @endif
    @if (session('error'))
        <script>
            toastr.error(@json(session('error')), @json(__('common.toast.error_title')));
        </script>
    @endif

    @stack('admin_js')
    @livewireScripts
</body>

</html>
