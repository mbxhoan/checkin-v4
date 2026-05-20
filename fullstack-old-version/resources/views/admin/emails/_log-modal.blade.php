@once
    {{-- Shared modal + styles for compact email logs. Place this OUTSIDE any auto-refreshed container. --}}
    <div class="modal fade" id="emailLogModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0">Log email</h5>
                        <div class="text-muted small" id="emailLogModalMeta"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <pre class="mb-0 small" id="emailLogModalBody" style="white-space: pre-wrap;"></pre>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Subtle highlight for rows that have error logs (avoid heavy red). */
        .email-row--has-error td {
            background-color: rgba(220, 53, 69, 0.04);
        }
        .email-row--has-error td:first-child {
            box-shadow: inset 3px 0 0 rgba(220, 53, 69, 0.35);
        }

        /* Compact log button + preview in the Log column. */
        .js-email-log.btn {
            padding: 0.15rem 0.35rem;
            font-size: 0.75rem;
            line-height: 1.2;
        }
        .email-log-preview {
            display: inline-block;
            max-width: 260px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>

    <script>
        (function () {
            if (window.__emailLogModalBound) return;
            window.__emailLogModalBound = true;

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.js-email-log');
                if (!btn) return;
                e.preventDefault();
                e.stopPropagation();

                const srcId = btn.getAttribute('data-log-source');
                const srcEl = srcId ? document.getElementById(srcId) : null;
                if (!srcEl) return;

                let payload = {};
                try { payload = JSON.parse(srcEl.textContent || '{}'); } catch (_) {}

                const metaEl = document.getElementById('emailLogModalMeta');
                const bodyEl = document.getElementById('emailLogModalBody');

                const meta = [
                    payload.to_email ? (payload.to_name ? `${payload.to_name} <${payload.to_email}>` : payload.to_email) : null,
                    payload.status ? `Status: ${payload.status}` : null,
                ].filter(Boolean).join(' · ');

                if (metaEl) metaEl.textContent = meta;
                if (bodyEl) bodyEl.textContent = payload.log || '(khong co log)';
            });
        })();
    </script>
@endonce
