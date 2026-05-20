@extends('layouts.app')

@section('content')
    <div class="row justify-content-center my-4">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h1 class="h4 mb-2">{{ $pageTitle }}</h1>
                    @if (!empty($pageDescription))
                        <p class="text-muted mb-3">{{ $pageDescription }}</p>
                    @endif

                    <div class="terms-scroll p-3" tabindex="0" aria-label="{{ $pageTitle }}">
                        <div class="terms-content" id="legalDocument">
                            {!! $content !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('register') }}" class="btn btn-primary">{{ __('register.public_terms.register') }}</a>
                <a href="{{ route('login') }}" class="btn btn-outline-secondary">{{ __('auth.login') }}</a>
            </div>
        </div>
    </div>

    @once
        <style>
            .terms-content {
                font-size: 0.95rem;
                line-height: 1.7;
            }

            .terms-content h1,
            .terms-content h2,
            .terms-content h3,
            .terms-content h4 {
                font-size: 1.05rem;
                font-weight: 700;
                margin-top: 1rem;
                margin-bottom: 0.5rem;
            }

            .terms-content p {
                margin-bottom: 0.6rem;
            }

            .terms-content ul {
                padding-left: 1.1rem;
            }

            .terms-content li {
                margin-bottom: 0.35rem;
            }

            .terms-scroll {
                max-height: min(70vh, 680px);
                overflow: auto;
                border: 1px solid #dce1ea;
                border-radius: 0.5rem;
                background: #fff;
            }
        </style>
    @endonce
@endsection
