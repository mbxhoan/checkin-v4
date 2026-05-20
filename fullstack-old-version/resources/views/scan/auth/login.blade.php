<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Scan</title>
    
    <!-- PWA Support -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="black">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">

    {{-- bootstrap --}}
    <link href="{{ asset('offlines/offline-css/5.3.0-bootstrap.min.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Đăng nhập</h3>
                    </div>
                    <div class="card-body">
                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('scan.login.post') }}">
                            @csrf

                            @include('components.form-groups.input-group', [
                                'id'                => "username",
                                'model'             => null,
                                'type'              => "text",
                                'label'             => "Username",
                                'formClass'         => 'form-group mb-3',
                                'placeholder'       => "Username",
                                'required'          => true,
                                'autofocus'         => true,
                            ])
                            @include('components.form-groups.input-group', [
                                'id'                => "password",
                                'model'             => null,
                                'type'              => "password",
                                'label'             => __('users.attributes.password'),
                                'formClass'         => 'mb-3',
                                'inputClass'        => 'form-control text-sm',
                                'placeholder'       => __('users.attributes.password'),
                                'required'          => true,
                            ])

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Đăng nhập</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
