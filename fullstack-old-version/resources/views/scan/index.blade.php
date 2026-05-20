@extends('scan.layouts.templates.page', [
    'pageTitle'         => "Danh sách sự kiện",
    'favicon'           => null,
    'popErrors'         => true,
    'form_width'        => 10,
    'form_class'        => 'scan-index-shell',
])

@section('meta-data')
    @include('components.metadata', [
        'title'         => "Danh sách sự kiện",
        'description'   => $description ?? config("metapage.description"),
        'robots'        => $url ??config("metapage.robots"),
        'url'           => url()->current(),
        'image'         => $metaImg ?? config("metapage.image"),
        'language'      => app()->getLocale(),
    ])
@endsection

@section('primary-content')
    <div class="scan-index">
        <div class="scan-index__top">
            <div class="scan-index__brand">
                <img
                    src="{{ asset(config('info.page.logo_1.internal_path') ?? 'assets/images/logo.png') }}"
                    alt="{{ config('app.name') }}"
                    class="scan-index__brand-logo"
                    loading="lazy"
                >
                <div>
                    <div class="scan-index__title">
                        Chọn sự kiện
                    </div>
                    <div class="scan-index__subtitle">
                        Chọn 1 sự kiện để mở màn hình scan/checkin.
                    </div>
                </div>
            </div>

            <div class="scan-index__user">
                <div class="scan-index__user-meta">
                    <div class="scan-index__user-name">
                        {{ auth()->user()->name }}
                    </div>
                    <div class="scan-index__user-email">
                        {{ auth()->user()->email }}
                    </div>
                </div>
                <a href="{{ route('scan.logout') }}"
                    class="btn btn-sm btn-primary scan-index__logout"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                >
                    @lang('auth.logout')
                    <x-icon name="right-from-bracket" />
                </a>
                <form id="logout-form" class="d-none" action="{{ route('scan.logout') }}" method="POST">
                    {{ csrf_field() }}
                </form>
            </div>
        </div>

        <div class="scan-index__tools">
            <div class="input-group scan-index__search">
                <span class="input-group-text">
                    <x-icon name="magnifying-glass" />
                </span>
                <input type="search"
                    id="eventSearch"
                    class="form-control"
                    placeholder="Tìm theo mã sự kiện, tên, mô tả..."
                    aria-label="Tìm sự kiện"
                    autocomplete="off"
                >
            </div>
            <div class="scan-index__count text-muted">
                <x-icon name="layer-group" />
                <span id="eventCount">{{ $events->count() }}</span> sự kiện
            </div>
        </div>

        @if ($events->count())
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 mt-1" id="eventGrid">
                @foreach ($events as $event)
                    @php
                        $searchText = trim(($event->code ?? '').' '.($event->name ?? '').' '.($event->description ?? ''));
                        $hasPrint = ($event->getEventSetting("ALLOW_CHECKIN_PRINT", null)->value ?? null)
                            && (!empty($event->labels) && $event->labels->count());
                        $labelName = $hasPrint ? $event->labels->first()->name : null;
                    @endphp
                    <div class="col scan-event-col" data-event-search="{{ Str::lower($searchText) }}">
                        <a href="{{ route('scan.scan', ['event' => $event]) }}"
                            class="scan-event-card-link text-decoration-none"
                        >
                            <x-card class="scan-event-card h-100">
                                <x-slot:title>
                                    <div class="scan-event-card__header">
                                        <div class="scan-event-card__code">
                                            <span class="badge text-bg-dark">
                                                {{ $event->code }}
                                            </span>
                                        </div>
                                        @if ($event->logo)
                                            <img
                                                src="{{ $event->logoUrl->getUrl() }}"
                                                alt="{{ $event->code }}"
                                                class="scan-event-card__logo"
                                                loading="lazy"
                                            >
                                        @else
                                            <div class="scan-event-card__logo-fallback" aria-hidden="true">
                                                <x-icon name="calendar-days" />
                                            </div>
                                        @endif
                                    </div>
                                </x-slot:title>

                                <div class="scan-event-card__name">
                                    {{ $event->name }}
                                </div>

                                @if (!empty($event->description))
                                    <div class="scan-event-card__desc text-muted">
                                        {{ Str::limit($event->description, 120) }}
                                    </div>
                                @endif

                                <div class="scan-event-card__meta">
                                    <div class="scan-event-card__meta-row">
                                        <x-icon name="calendar" prefix="fa-regular" />
                                        <span>
                                            {{ humanize_date($event->from_date, 'd-m-Y') }}
                                            <span class="text-muted">-</span>
                                            {{ humanize_date($event->to_date, 'd-m-Y') }}
                                        </span>
                                    </div>
                                    @if ($hasPrint)
                                        <div class="scan-event-card__meta-row">
                                            <x-icon name="print" />
                                            <span>
                                                {{ $labelName }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <x-slot:footer>
                                    <div class="scan-event-card__footer">
                                        <span class="scan-event-card__cta">
                                            Vào checkin
                                            <x-icon name="arrow-right" />
                                        </span>
                                    </div>
                                </x-slot:footer>
                            </x-card>
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="scan-index__empty">
                <div class="scan-index__empty-icon" aria-hidden="true">
                    <x-icon name="calendar-xmark" />
                </div>
                <div class="scan-index__empty-title">
                    Chưa có sự kiện nào
                </div>
                <div class="scan-index__empty-subtitle text-muted">
                    Vui lòng liên hệ quản trị để được cấp quyền truy cập sự kiện.
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scan_js')
    <script>
        (function () {
            const input = document.getElementById('eventSearch');
            const grid = document.getElementById('eventGrid');
            const countEl = document.getElementById('eventCount');

            if (!input || !grid || !countEl) return;

            const items = Array.from(grid.querySelectorAll('[data-event-search]'));
            const normalize = (s) => (s || '').toString().toLowerCase().trim();

            const applyFilter = () => {
                const q = normalize(input.value);
                let visible = 0;

                for (const el of items) {
                    const hay = normalize(el.getAttribute('data-event-search'));
                    const ok = !q || hay.includes(q);
                    el.style.display = ok ? '' : 'none';
                    if (ok) visible++;
                }

                countEl.textContent = visible;
            };

            input.addEventListener('input', applyFilter, { passive: true });
        })();
    </script>
@endpush

@push('scan_css')
    <style>
        .scan-index-shell {
            padding-top: 28px;
        }

        .scan-index__top {
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding: 6px 2px 16px 2px;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        }

        .scan-index__brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .scan-index__brand-logo {
            width: 54px;
            height: 54px;
            object-fit: contain;
            border-radius: 14px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.02), rgba(15, 23, 42, 0.00));
            padding: 6px;
        }

        .scan-index__title {
            font-weight: 800;
            letter-spacing: -0.02em;
            font-size: 1.25rem;
            line-height: 1.1;
            color: #0f172a;
        }

        .scan-index__subtitle {
            margin-top: 4px;
            font-size: 0.9rem;
            color: rgba(15, 23, 42, 0.70);
        }

        .scan-index__user {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 14px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: rgba(15, 23, 42, 0.02);
        }

        .scan-index__user-name {
            font-weight: 700;
            font-size: 0.95rem;
            color: #0f172a;
            line-height: 1.1;
        }

        .scan-index__user-email {
            font-size: 0.85rem;
            color: rgba(15, 23, 42, 0.68);
            line-height: 1.1;
        }

        .scan-index__logout {
            border-radius: 999px;
            padding-left: 14px;
            padding-right: 14px;
            font-weight: 700;
            box-shadow: 0 12px 30px rgba(13, 110, 253, 0.20);
        }

        .scan-index__tools {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding-top: 14px;
        }

        .scan-index__search .input-group-text,
        .scan-index__search .form-control {
            border-radius: 12px;
        }

        .scan-index__search .input-group-text {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            background: rgba(15, 23, 42, 0.02);
            border: 1px solid rgba(15, 23, 42, 0.10);
        }

        .scan-index__search .form-control {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            border: 1px solid rgba(15, 23, 42, 0.10);
        }

        .scan-index__count {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .scan-event-card {
            border-radius: 16px;
            border: 1px solid rgba(15, 23, 42, 0.10);
            overflow: hidden;
            transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
        }

        .scan-event-card-link:hover .scan-event-card,
        .scan-event-card-link:focus-visible .scan-event-card {
            transform: translateY(-3px);
            border-color: rgba(13, 110, 253, 0.35);
            box-shadow: 0 18px 55px rgba(15, 23, 42, 0.10);
        }

        .scan-event-card .card-header {
            background: linear-gradient(180deg, rgba(13, 110, 253, 0.08), rgba(13, 110, 253, 0.02));
            border-bottom: 1px solid rgba(13, 110, 253, 0.08);
        }

        .scan-event-card__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .scan-event-card__logo {
            width: 42px;
            height: 42px;
            object-fit: contain;
            border-radius: 12px;
            background: #fff;
            border: 1px solid rgba(15, 23, 42, 0.08);
            padding: 4px;
        }

        .scan-event-card__logo-fallback {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px dashed rgba(15, 23, 42, 0.18);
            color: rgba(15, 23, 42, 0.6);
            background: rgba(255, 255, 255, 0.65);
        }

        .scan-event-card__name {
            font-weight: 800;
            letter-spacing: -0.01em;
            color: #0f172a;
            line-height: 1.2;
            margin-bottom: 6px;
        }

        .scan-event-card__desc {
            font-size: 0.95rem;
            line-height: 1.4;
        }

        .scan-event-card__meta {
            margin-top: 12px;
            display: grid;
            gap: 6px;
            color: rgba(15, 23, 42, 0.75);
            font-size: 0.92rem;
        }

        .scan-event-card__meta-row {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .scan-event-card .card-footer {
            background: rgba(15, 23, 42, 0.02);
            border-top: 1px solid rgba(15, 23, 42, 0.08);
        }

        .scan-event-card__footer {
            display: flex;
            justify-content: flex-end;
        }

        .scan-event-card__cta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 800;
            color: #0d6efd;
        }

        .scan-index__empty {
            margin-top: 18px;
            border: 1px dashed rgba(15, 23, 42, 0.18);
            border-radius: 18px;
            padding: 28px 18px;
            text-align: center;
            background: rgba(15, 23, 42, 0.02);
        }

        .scan-index__empty-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.05);
            color: rgba(15, 23, 42, 0.70);
            margin-bottom: 10px;
        }

        .scan-index__empty-title {
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.02em;
            font-size: 1.05rem;
        }

        .scan-index__empty-subtitle {
            margin-top: 4px;
            font-size: 0.92rem;
        }

        @media (min-width: 768px) {
            .scan-index__top {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }

            .scan-index__user {
                width: auto;
                min-width: 320px;
                justify-content: flex-end;
            }

            .scan-index__tools {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }

            .scan-index__search {
                max-width: 520px;
            }
        }
    </style>
@endpush
