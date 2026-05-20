<table class="table">
    <caption>Tổng hợp</caption>
    <thead>
        <tr>
            <th scope="col">ID</th>
            <th scope="col" class="col-2">Message ID</th>
            <th scope="col" class="col-2">Email</th>
            <th scope="col" class="col-2">Họ tên</th>
            <th scope="col">Thời gian xử lý</th>
            <th scope="col">Log</th>
            <th scope="col">Trạng thái</th>
            <th scope="col"></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($emails as $email)
            @php
                $rawLog = $email->error_log;
                $logArr = null;
                $logPretty = null;

                if (is_string($rawLog)) {
                    $decoded = json_decode($rawLog, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $logArr = $decoded;
                    } else {
                        $logPretty = $rawLog;
                    }
                } elseif (is_array($rawLog)) {
                    $logArr = $rawLog;
                } elseif (is_object($rawLog)) {
                    $logArr = (array) $rawLog;
                }

                if ($logArr !== null) {
                    $logPretty = json_encode($logArr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }

                $logMessage = is_array($logArr) ? ($logArr['message'] ?? $logArr['error'] ?? null) : null;
                $logPreview = $logMessage
                    ? \Illuminate\Support\Str::limit((string) $logMessage, 80)
                    : ($logPretty ? \Illuminate\Support\Str::limit(preg_replace('/\\s+/', ' ', (string) $logPretty), 80) : null);

                $hasLog = !empty($rawLog) && $rawLog !== 'null';
                $isErrorRow = $hasLog && ($email->status !== $email::STATUS_SENT);

                // Keep payload as a PHP variable to avoid edge-case Blade parse issues in multiline @json([...]).
                $logPayload = [
                    'to_email' => $email->to_email,
                    'to_name'  => $email->to_name,
                    'status'   => $email->status,
                    'log'      => $logPretty,
                ];
            @endphp
            <tr id="{{ $email->id }}"
                class="text-sm {{ $email->status == $email::STATUS_CLOSED ? 'table-secondary' : ($email->message_id ? 'table-light' : '') }} {{ $isErrorRow ? 'email-row--has-error' : '' }}"
                data-bs-toggle="collapse" data-bs-target="#collapseWebhook{{ $email->message_id }}"
                aria-expanded="false" aria-controls="collapseWebhook{{ $email->message_id }}">
                <th scope="row" class="text-xs" style="vertical-align: middle;">
                    {{ $email->id }}
                </th>
                <td class="text-xs" style="vertical-align: middle;">
                    {{ $email->message_id }}
                </td>
                <td>
                    {{ $email->to_email }}
                </td>
                <td>
                    {{ $email->to_name }}
                    <a href="" data-bs-toggle="modal" data-bs-target="#{{ $email->id }}Modal">
                        <x-icon name="circle-info" />
                    </a>
                    @include('admin.emails._modal-info', [
                        'modalId' => "{$email->id}Modal",
                        'data' => $email->param,
                    ])
                </td>
                <td id="email-sent_at-{{ $email->id }}">
                    {{ $email->sent_at ? humanize_date($email->sent_at, 'H:i:s d-m-Y') : null }}
                </td>
                <td>
                    @if ($hasLog)
                        <button
                            type="button"
                            class="btn btn-xs {{ $isErrorRow ? 'btn-outline-danger' : 'btn-outline-secondary' }} js-email-log"
                            data-bs-toggle="modal"
                            data-bs-target="#emailLogModal"
                            data-log-source="email-log-{{ $email->id }}"
                            title="{{ $logPreview }}"
                        >
                            <x-icon name="circle-info" />
                            Log
                        </button>

                        <script type="application/json" id="email-log-{{ $email->id }}">
                            @json($logPayload)
                        </script>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td id="email-status-{{ $email->id }}">
                    @include('admin.emails._status', [
                        'email' => $email,
                        'class' => 'btn btn-sm',
                    ])
                </td>
                <td id="btns-status-{{ $email->id }}">
                    @include('admin.emails._btn-status', [
                        'email' => $email,
                    ])
                </td>
            </tr>
            @if (!empty($email->webhookPostmarks) && $email->webhookPostmarks->count())
                @include('admin.emails.tables._webhook-postmark', [
                    'colspan' => 8,
                    'email' => $email,
                ])
            @endif
        @endforeach
    </tbody>
</table>
