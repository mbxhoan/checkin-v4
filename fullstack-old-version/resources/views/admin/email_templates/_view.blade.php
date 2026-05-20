@if (!empty($template) && count($template))
    @php
        $isLocked = (bool) ($template['is_template_locked'] ?? false);
        $canPreviewLockedContent = (bool) ($template['can_preview_locked_content'] ?? false);
        $paidMeta = (array) ($template['paid_template'] ?? []);
        $templateId = (int) ($template['TemplateId'] ?? 0);
    @endphp

    @if ($isLocked && !$canPreviewLockedContent)
        <div class="alert alert-warning mb-3">
            <h5 class="mb-2">Template này đang khoá</h5>
            <div class="small">
                @if (!empty($paidMeta['event_name']))
                    <div>Sự kiện: <strong>{{ $paidMeta['event_name'] }}</strong></div>
                @endif
                @if (!empty($paidMeta['event_time']))
                    <div>Thời gian: <strong>{{ $paidMeta['event_time'] }}</strong></div>
                @endif
                @if (!empty($paidMeta['credit']))
                    <div>Credit: <strong>{{ $paidMeta['credit'] }}</strong></div>
                @endif
            </div>
            <button
                type="button"
                class="btn btn-sm btn-warning mt-3 btn-request-template-unlock"
                data-url="{{ route('admin.email_templates.request-unlock', $templateId) }}"
            >
                Đăng ký mở khoá
            </button>
        </div>
    @else
        @if ($isLocked && $canPreviewLockedContent)
            <div class="alert alert-warning mb-3">
                <h5 class="mb-2">Template này đang khoá</h5>
                <div class="small">
                    Bạn chỉ có quyền xem trước nội dung. Để chỉnh sửa hoặc nhân bản, vui lòng mở khoá template.
                </div>
                <button
                    type="button"
                    class="btn btn-sm btn-warning mt-3 btn-request-template-unlock"
                    data-url="{{ route('admin.email_templates.request-unlock', $templateId) }}"
                >
                    Đăng ký mở khoá
                </button>
            </div>
        @endif

        <h5 class="fw-bold mb-4">
            Subject/Tiêu đề: {{ $template['Subject'] }}
        </h5>
        <div class="d-none">
            <input
                type="hidden"
                name=""
                class="input-full-html-body d-none"
                id="{{ $template['TemplateId'] }}"
                value="{{ $template['HtmlBody'] }}"
            >
        </div>
        <div class="w-100">
            {!! $template['HtmlBody'] !!}
        </div>
    @endif
@endif

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
