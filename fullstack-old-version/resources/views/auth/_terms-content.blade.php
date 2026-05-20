@once
    @push('css')
        <style>
            .terms-content {
                color: #0f172a;
                font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans",
                    "Liberation Sans", sans-serif;
                font-size: 0.95rem;
                line-height: 1.65;
            }

            .terms-content p {
                margin: 0 0 0.8rem;
            }

            .terms-content p.p1 {
                text-align: center;
                font-size: 1.15rem;
                font-weight: 800;
                margin: 0 0 0.25rem;
            }

            /* Update date line */
            .terms-content > p:nth-child(2) {
                text-align: center;
                color: #64748b;
                font-style: italic;
                font-weight: 600;
                background: transparent;
                border: 0;
                padding: 0;
                margin: 0 0 1rem;
            }

            .terms-content > p:nth-child(2) b,
            .terms-content > p:nth-child(2) strong {
                font-weight: 600;
            }

            /* Section headings */
            .terms-content p.p2 {
                margin: 1.25rem 0 0.5rem;
                font-size: 1.02rem;
                font-weight: 800;
                padding: 0.45rem 0.6rem;
                background: rgba(15, 23, 42, 0.03);
                border: 1px solid rgba(15, 23, 42, 0.06);
                border-radius: 0.6rem;
            }

            /* Paragraph body */
            .terms-content p.p3,
            .terms-content p.p8 {
                margin-left: 0.75rem;
            }

            .terms-content ul {
                margin: 0 0 0.9rem 2.1rem;
                padding: 0;
            }

            .terms-content li {
                margin: 0 0 0.35rem;
            }

            .terms-content b,
            .terms-content strong {
                font-weight: 800;
            }

            .terms-content p.p7,
            .terms-content p.p9,
            .terms-content p.p10,
            .terms-content p.p11 {
                display: none;
            }

            .terms-scroll {
                max-height: min(65vh, 560px);
                overflow: auto;
                -webkit-overflow-scrolling: touch;
                background: #fff;
                border: 1px solid rgba(0, 0, 0, 0.08);
                border-radius: 0.75rem;
            }

            .terms-scroll:focus {
                outline: none;
                box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
            }
        </style>
    @endpush
@endonce

@php
    $locale = app()->getLocale();
    $termsContentPaths = config('register.terms.content_paths');
    $termsRelativePath = is_array($termsContentPaths)
        ? ($termsContentPaths[$locale] ?? $termsContentPaths['vi'] ?? $termsContentPaths['en'] ?? null)
        : null;

    if (!$termsRelativePath) {
        $termsRelativePath = config('register.terms.content_path', 'content/terms-of-use.html');
    }

    $termsContentPath = resource_path($termsRelativePath);
    $termsContent = \Illuminate\Support\Facades\File::exists($termsContentPath)
        ? \Illuminate\Support\Facades\File::get($termsContentPath)
        : __('register.terms.content_fallback');
@endphp

<div class="terms-content" id="termsDocument">
    {!! $termsContent !!}
</div>
