@extends('layouts.app')

@section('content')
    <div class="row justify-content-center my-4">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="terms-scroll p-3" tabindex="0" aria-label="{{ __('register.terms.accept_page_aria') }}">
                        @include('auth._terms-content')
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('register') }}" class="btn btn-primary">{{ __('register.public_terms.register') }}</a>
                <a href="{{ route('login') }}" class="btn btn-outline-secondary">{{ __('auth.login') }}</a>
            </div>
        </div>
    </div>
@endsection
