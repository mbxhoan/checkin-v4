@php
    $directPrintConfig = [
        'enabled' => (bool) config('services.direct_print.enabled', false),
        'provider' => (string) config('services.direct_print.provider', 'browser'),
        'fallback_to_browser' => (bool) config('services.direct_print.fallback_to_browser', true),
        'qz_printer' => config('services.direct_print.qz_printer'),
    ];
    $qzScriptUrl = (string) config('services.direct_print.qz_script_url', '');
@endphp

<script>
    window.__DELFI_DIRECT_PRINT__ = @json($directPrintConfig);
</script>

@if ($directPrintConfig['enabled'] && $directPrintConfig['provider'] === 'qz' && $qzScriptUrl !== '')
    <script src="{{ $qzScriptUrl }}"></script>
@endif
