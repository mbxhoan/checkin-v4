@extends('layouts.app')

@section('content')
    <div class="row justify-content-center my-4">
        <div class="col-md-10 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="alert alert-info small mb-3" id="termsScrollHint">
                        {{ __('register.terms.accept_page_scroll_hint') }}
                    </div>

                    <div class="terms-scroll p-3" id="termsScrollable" tabindex="0" aria-label="{{ __('register.terms.accept_page_aria') }}">
                        @include('auth._terms-content')
                    </div>
                </div>
            </div>

            <form action="{{ route('terms.accept.submit') }}" method="POST" class="card border-0 shadow-sm mt-3">
                @csrf

                <div class="card-body">
                    <div class="form-check mb-3">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            value="1"
                            id="accept_terms"
                            name="accept_terms"
                            required
                            disabled
                        >
                        <label class="form-check-label" for="accept_terms">
                            {{ __('register.terms.accept_page_checkbox') }}
                        </label>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary" id="termsAcceptSubmit" disabled>{{ __('register.terms.accept_page_submit') }}</button>
                        <a href="{{ route('logout') }}"
                           class="btn btn-outline-secondary"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{ __('auth.logout') }}
                        </a>
                    </div>
                </div>
            </form>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const scrollEl = document.getElementById('termsScrollable');
            const hintEl = document.getElementById('termsScrollHint');
            const acceptEl = document.getElementById('accept_terms');
            const submitEl = document.getElementById('termsAcceptSubmit');

            if (!scrollEl || !acceptEl || !submitEl) return;

            function isAtBottom() {
                const threshold = 2;
                return (scrollEl.scrollTop + scrollEl.clientHeight) >= (scrollEl.scrollHeight - threshold);
            }

            function updateState() {
                const scrollable = scrollEl.scrollHeight > (scrollEl.clientHeight + 2);
                const atBottom = !scrollable || isAtBottom();

                acceptEl.disabled = !atBottom;
                if (!atBottom) acceptEl.checked = false;

                submitEl.disabled = !atBottom || !acceptEl.checked;
                if (hintEl) hintEl.classList.toggle('d-none', atBottom);
            }

            scrollEl.addEventListener('scroll', updateState);
            acceptEl.addEventListener('change', updateState);
            updateState();
        });
    </script>
@endpush
