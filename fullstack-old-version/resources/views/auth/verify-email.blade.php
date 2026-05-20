@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <x-card>
                    <x-slot:title>
                        {{ __('auth.messages.verify_email_title') }}
                    </x-slot>

                    @if (session('status') == 'verification-link-sent')
                        <x-alert type="success">
                            {{ __('auth.messages.verify_email_sent') }}
                        </x-alert>
                    @endif

                    {{ __('auth.messages.verify_email_wait_message') }}

                    {{-- nếu chưa nhận được email --}}
                    {{-- @lang('If you did not receive the email'),

                    <form action="{{ route('verification.send') }}" method="POST" class="d-inline" role="form">
                        @csrf

                        <input type="submit" class="btn btn-link p-0 m-0 align-baseline" value="@lang('click here to request another')">
                    </form> --}}
                </x-card>
            </div>
        </div>
    </div>
@endsection
