@php
    $owner = config('legal.owner', []);
    $companyName = $owner['company_name'] ?? config('info.company_name');
    $taxCode = $owner['tax_code'] ?? null;
    $address = $owner['address'] ?? null;
    $hotline = $owner['hotline'] ?? null;
@endphp

<footer class="border-top bg-white {{ $class ?? 'py-3' }}">
    <div class="container">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div class="small text-muted">
                <div class="fw-semibold text-dark mb-1">{{ $companyName }}</div>
                @if (!empty($taxCode))
                    <div>{{ __('legal.owner.tax_code') }}: {{ $taxCode }}</div>
                @endif
                @if (!empty($address))
                    <div>{{ __('legal.owner.address') }}: {{ $address }}</div>
                @endif
                @if (!empty($hotline))
                    <div>{{ __('legal.owner.hotline') }}: {{ $hotline }}</div>
                @endif
            </div>

            <div class="d-flex flex-wrap align-items-start justify-content-lg-end gap-3 small">
                <a href="{{ route('terms.public') }}" class="link-secondary">{{ __('legal.links.terms') }}</a>
                <a href="{{ route('legal.privacy') }}" class="link-secondary">{{ __('legal.links.privacy') }}</a>
                <a href="{{ route('legal.payment-refund') }}" class="link-secondary">{{ __('legal.links.payment_refund') }}</a>
            </div>
        </div>
    </div>
</footer>
