
@extends('admin.layouts.templates.page', [
    'pageTitle' => "Templates"
])

@section('title', __('campaigns.email_template.title'))

@section('buttons')
    <div class="buttons">
        @sys_admin
            <a href="{{ route('admin.email_templates.re-sync-postmark-templates') }}" class="">
                <x-icon name="rotate" />
            </a>
        @endsys_admin
    </div>
@endsection

@section('primary-content')
    @php
        $sortedTemplates = collect($templates ?? [])
            ->filter(fn ($template) => is_array($template) && count($template))
            ->sortBy([
                fn ($template) => ! (bool) ($template['is_paid_template'] ?? false),
                fn ($template) => (int) ($template['TemplateId'] ?? 0),
            ])
            ->values();
    @endphp

    <div class="d-flex justify-content-end mb-2">
        <div class="d-flex align-items-center gap-2">
            <label for="template-type-filter" class="mb-0 text-muted small w-25">Loại template</label>
            <select id="template-type-filter" class="form-select form-select-sm" style="min-width: 180px;">
                <option value="all" selected>Tất cả</option>
                <option value="free">Miễn phí</option>
                <option value="paid">Trả phí</option>
            </select>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="text-xs text-muted" id="template-sync-summary"></div>
        <div class="progress flex-grow-1 ms-3" style="height: 6px; max-width: 320px;">
            <div class="progress-bar" id="template-sync-progress" role="progressbar" style="width: 0%"></div>
        </div>
    </div>
    <div class="row g-2">
        <div class="col-md-3 mb-3 text-sm">
            <div
                style="max-width: 225px !important; height: 225px !important; overflow-y: hidden;"
                class="bg-white border rounded shadow-sm p-3 mx-auto"
            >
                <a href="{{ route('admin.email_templates.create') }}" class="mx-auto d-flex align-items-center justify-content-center h-100" title="{{ __('campaigns.email_template.add_new_tooltip') }}">
                    <x-icon name="plus" prefix="fa-solid" class="fa-2x" />
                </a>
            </div>
        </div>
        @foreach ($sortedTemplates as $template)
            @if (count($template))
                @php
                    $templateId = (int) ($template['TemplateId'] ?? 0);
                    $isLocked = (bool) ($template['is_template_locked'] ?? false);
                    $isPaidTemplate = (bool) ($template['is_paid_template'] ?? false);
                    $paidMeta = (array) ($template['paid_template'] ?? []);
                @endphp
                <div
                    class="col-lg-3 col-md-4 col-12 mb-3 text-sm js-template-item"
                    data-template-type="{{ $isPaidTemplate ? 'paid' : 'free' }}"
                >
                    <div
                        id="template-box-{{ $templateId }}"
                        data-template-id="{{ $templateId }}"
                        style="max-width: 225px !important; min-height: 225px !important;"
                        class="bg-white border rounded shadow-sm p-3 mx-auto"
                    >
                        <div class="row g-2 align-items-center">
                            <div class="col-md-7">
                                <div class="" style="width: 95% !important; white-space: nowrap;
                                    overflow: hidden;
                                    text-overflow: ellipsis;
                                    cursor: pointer;"
                                >
                                    {{ $template['Name'] ?? "UNNAMED" }}
                                </div>
                            </div>
                            <div class="col text-end">
                                <span id="template-sync-{{ $template['TemplateId'] }}" class="d-none me-1 template-sync-indicator" title="Đang đồng bộ...">
                                    <span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></span>
                                </span>
                                <a href="#" title="Nhân bản" class="template-card" data-id="{{ $template['TemplateId'] }}">
                                    <x-icon name="eye" />
                                </a>
                                @admin
                                    <a href="#" title="Nhân bản" data-bs-toggle="modal" data-bs-target="#confirmCloneModal-{{ $template['TemplateId'] }}">
                                        <x-icon name="clone" />
                                    </a>
                                    <!-- Modal Xác nhận Reset -->
                                    <div class="modal fade" id="confirmCloneModal-{{ $template['TemplateId'] }}" tabindex="-1" aria-labelledby="confirmCloneModal-{{ $template['TemplateId'] }}Label" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="confirmCloneModal-{{ $template['TemplateId'] }}Label">Xác nhận Nhân bản</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.email_templates.clone-postmark-template') }}" class="d-inline text-start"
                                                >
                                                    @csrf
                                                    @method('POST')
                                                    <div class="modal-body">
                                                        <p>
                                                            Bạn có chắc chắn muốn nhân bản nội dung mail này?
                                                        </p>
                                                        <input type="hidden" name="template_id" value="{{ $template['TemplateId'] }}">
                                                        <div class="row my-2">
                                                            @include('components.form-groups.input-group', [
                                                                'id'                => "name",
                                                                'fieldName'         => "name",
                                                                'value'             => "Copy - ".$template['Name'],
                                                                'label'             => 'Tên nội dung mail mới',
                                                                'type'              => "text",
                                                                'formClass'         => 'mb-3 col-md-12',
                                                                'required'          => true,
                                                            ])
                                                        </div>
                                                        <div class="row my-2">
                                                            @include('components.form-groups.input-group', [
                                                                'id'                => "confirm",
                                                                'fieldName'         => "confirm",
                                                                'value'             => null,
                                                                'label'             => 'VUI LÒNG NHẬP <b>"COPY"</b> ĐỂ XÁC NHẬN NHÂN BẢN',
                                                                'type'              => "text",
                                                                'formClass'         => 'mb-3 col-md-12',
                                                                'required'          => true,
                                                            ])
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('common.cancel')</button>
                                                        <button type="submit" class="btn btn-danger">Xác nhận</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endadmin
                                <a target="_blank" href="{{ route('admin.email_templates.view-postmark-template', $template['TemplateId']) }}" class="">
                                    <x-icon name="up-right-from-square" />
                                </a>
                                <a href="{{ route('admin.email_templates.edit-postmark-template', $template['TemplateId']) }}"
                                class="btn btn-light btn-sm border" title="Chỉnh sửa">
                                    <x-icon name="edit" />
                                </a>
                            </div>
                        </div>

                        <div class="small text-muted mb-2 text-truncate">
                            Subject: <span class="text-body-secondary">{{ $template['Subject'] ?? '—' }}</span>
                        </div>

                        <div class="mb-2 d-flex flex-wrap gap-1">
                            @if ($isPaidTemplate)
                                <span class="badge text-bg-info">Trả phí</span>
                            @endif
                            @if ($isLocked)
                                <span class="badge text-bg-warning">Đang khoá</span>
                            @endif
                        </div>

                        @if ($isPaidTemplate)
                            <div class="small mb-2">
                                @if (!empty($paidMeta['event_name']))
                                    <div class="text-muted">
                                        Sự kiện: <span class="text-dark">{{ $paidMeta['event_name'] }}</span>
                                    </div>
                                @endif
                                @if (!empty($paidMeta['event_time']))
                                    <div class="text-muted">
                                        Thời gian: <span class="text-dark">{{ $paidMeta['event_time'] }}</span>
                                    </div>
                                @endif
                                @if (!empty($paidMeta['credit']))
                                    <div class="text-muted">
                                        Credit: <span class="text-dark">{{ $paidMeta['credit'] }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if ($isLocked)
                            <button
                                type="button"
                                class="btn btn-xs btn-warning mb-2 btn-request-template-unlock"
                                data-url="{{ route('admin.email_templates.request-unlock', $templateId) }}"
                            >
                                Đăng ký mở khoá
                            </button>
                        @endif

                        @sys_admin
                            <div class="fw-bold" style="max-width: 260px !important; white-space: nowrap;
                                overflow: hidden;
                                text-overflow: ellipsis;
                                cursor: pointer;"
                            >
                                Alias:
                                <span class="fst-italic">
                                    {{ $template['Alias'] }}
                                </span>
                            </div>
                            <div class="fw-bold">
                                Postmark ID:
                                <span id="postmark-template-id-{{ $template['TemplateId'] }}">
                                    {{ $template['TemplateId'] }}
                                </span>
                                @include('components.btn-copy', [
                                    'class'     => '',
                                    'targetId'  => "postmark-template-id-{$template['TemplateId']}"
                                ])
                            </div>
                            <div class="">
                                TemplateType: {{ $template['TemplateType'] }}
                            </div>
                            <div class="">
                                LayoutTemplate: {{ $template['LayoutTemplate'] }}
                            </div>
                        @endsys_admin

                        <div class="fw-bold" style="max-width: 260px !important; white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                            cursor: pointer;"
                        >
                            Subject: {{ $template['Subject'] ?? null }}
                        </div>

                        <div class="d-none">
                            <input
                                type="hidden"
                                name=""
                                class="input-full-html-body d-none"
                                id="{{ $templateId }}"
                                value="{{ $template['HtmlBody'] ?? '' }}"
                            >
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        <div id="template-filter-empty" class="col-12 text-center text-muted py-4 d-none">
            Không có template phù hợp với bộ lọc hiện tại.
        </div>
    </div>

    {{-- Modal giữ nguyên --}}
    <div class="modal fade" id="templateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xem trước</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body" id="templateModalBody">
                    Loading template details...
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('admin_js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterSelect = document.getElementById('template-type-filter');
            const cards = Array.from(document.querySelectorAll('.js-template-item'));
            const emptyBlock = document.getElementById('template-filter-empty');

            if (!filterSelect || !cards.length) {
                return;
            }

            const applyFilter = () => {
                const selectedType = filterSelect.value || 'all';
                let visibleCount = 0;

                cards.forEach((card) => {
                    const templateType = card.getAttribute('data-template-type') || 'free';
                    const isVisible = selectedType === 'all' || selectedType === templateType;

                    card.classList.toggle('d-none', !isVisible);
                    if (isVisible) {
                        visibleCount++;
                    }
                });

                if (emptyBlock) {
                    emptyBlock.classList.toggle('d-none', visibleCount > 0);
                }
            };

            filterSelect.addEventListener('change', applyFilter);
            applyFilter();
        });
    </script>
    <script>
        $(document).on('click', '.btn-request-template-unlock', function (event) {
            event.preventDefault();

            const $button = $(this);
            const url = $button.data('url');
            if (!url || $button.prop('disabled')) {
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            $button.prop('disabled', true);

            $.ajax({
                url: url,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                success: function (response) {
                    toastr.success(response.message || 'Đăng ký mở khoá thành công.');
                },
                error: function (xhr) {
                    const message = xhr?.responseJSON?.message || 'Đăng ký mở khoá thất bại.';
                    toastr.error(message);
                },
                complete: function () {
                    $button.prop('disabled', false);
                }
            });
        });

        $('.template-card').on('click', function () {
            let templateId = $(this).data('id');

            // Uncheck other checkboxes, check this one
            $('.template-checkbox').prop('checked', false);
            $('#template_' + templateId).prop('checked', true);

            // Show modal
            $('#templateModal').modal('show');
            $('#templateModalBody').html('Loading...');

            // Load template details from controller
            $.ajax({
                url: '/admin/email_templates/get-postmark-templates/' + templateId, // <-- your route to get template detail
                method: 'GET',
                success: function (data) {
                    $('#templateModalBody').html(data); // render view or html from controller
                },
                error: function () {
                    $('#templateModalBody').html('<div class="text-danger">Error loading template details</div>');
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const boxes = Array.from(document.querySelectorAll('[data-template-id]'));
            const templateIds = boxes.map((el) => el.getAttribute('data-template-id')).filter(Boolean);

            const total = templateIds.length;
            if (!total) return;

            const $summary = $('#template-sync-summary');
            const $progress = $('#template-sync-progress');

            let done = 0;
            let cursor = 0;
            let inFlight = 0;
            const maxConcurrent = 2; // avoid spamming Postmark API
            const force = @json($forceSync ?? false) ? 1 : 0;

            const updateProgress = () => {
                const percent = total > 0 ? Math.round((done / total) * 100) : 0;
                $summary.text('{{ __('campaigns.email_template.syncing') }} templates... (' + done + '/' + total + ')');
                $progress.css('width', `${percent}%`).attr('aria-valuenow', percent);
                if (done >= total) {
                    $summary.text('{{ __('campaigns.email_template.synced') }} templates (' + total + '/' + total + ')');
                }
            };

            const setIndicator = (templateId, state) => {
                const $el = $(`#template-sync-${templateId}`);
                if (!$el.length) return;

                switch (state) {
                    case 'loading':
                        $el.removeClass('d-none').attr('title', '{{ __('campaigns.email_template.syncing') }}');
                        $el.html('<span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></span>');
                        break;
                    case 'done':
                        $el.removeClass('d-none').attr('title', '{{ __('campaigns.email_template.synced') }}');
                        $el.html('<i class="fa-solid fa-circle-check text-success"></i>');
                        break;
                    case 'error':
                        $el.removeClass('d-none').attr('title', '{{ __('campaigns.email_template.sync_error') }}');
                        $el.html('<i class="fa-solid fa-circle-exclamation text-danger"></i>');
                        break;
                    default:
                        $el.addClass('d-none');
                }
            };

            const runNext = () => {
                while (inFlight < maxConcurrent && cursor < total) {
                    const templateId = templateIds[cursor++];
                    inFlight++;
                    setIndicator(templateId, 'loading');

                    $.ajax({
                        url: `/admin/email_templates/sync-postmark-template-async/${templateId}`,
                        method: 'GET',
                        data: { force },
                    }).done(function () {
                        setIndicator(templateId, 'done');
                    }).fail(function () {
                        setIndicator(templateId, 'error');
                    }).always(function () {
                        done++;
                        inFlight--;
                        updateProgress();
                        runNext();
                    });
                }
            };

            updateProgress();
            runNext();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Select all iframes with the class 'html-template-preview'
            const iframes = document.querySelectorAll('iframe.html-template-preview');

            iframes.forEach(iframe => {
                // Get the TemplateId from the iframe's ID
                const templateId = iframe.id;

                // Find the corresponding input element with the matching TemplateId
                const input = document.querySelector(`input.input-full-html-body[id="${templateId}"]`);

                if (input) {
                    // Get the HTML content from the input's value
                    const html = input.value;

                    // Write the HTML content into the iframe
                    const doc = iframe.contentDocument || iframe.contentWindow.document;
                    doc.open();
                    doc.write(html);
                    doc.close();
                }
            });
        });
    </script>
@endpush
