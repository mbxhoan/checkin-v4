@yield('css')

<!-- Bootstrap Css -->
<link href="{{ asset('build/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
<!-- Icons Css -->
<link href="{{ asset('build/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
<!-- App Css-->
<link href="{{ asset('build/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
<!-- App js -->
{{-- <script src="{{ asset('build/js/plugin.js') }}"></script> --}}

<!-- DataTables CSS -->
<link rel="stylesheet" href="{{ asset('offlines/offline-css/1.13.6-dataTables.bootstrap5.min.css') }}">
<!-- Boostrap -->
<link href="{{ asset('offlines/offline-css/5.3.5-bootstrap.min.css') }}" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- Select 2 -->
<link href="{{ asset('offlines/offline-css/4.1.0-select2.min.css') }}" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />