<?php

namespace App\Services\Admin;

use App\Models\BaseModel;
use App\Models\Checkin;
use App\Models\Client;
use App\Models\Event;
use App\Models\N8nChatIssueReport;
use App\Models\N8nChatMessage;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class N8nChatbotAgentService
{
    const CATEGORY_QUICK_REPORT = 'quick_report';
    const CATEGORY_STATISTICS = 'statistics';
    const CATEGORY_REPORT_WITH_CHART = 'report_with_chart';
    const CATEGORY_EXPORT = 'export';
    const CATEGORY_ISSUE_SUPPORT = 'issue_support';

    public function detectModeFromMessage(string $message): ?string
    {
        $normalized = $this->normalizeText($message);

        if ($this->isIssueRelatedMessage($message)) {
            return 'SUPPORT';
        }

        $reportHints = [
            'bao cao',
            'thong ke',
            'bieu do',
            'tong hop',
            'bao nhieu',
            'su kien',
            'khach hang',
            'xuat file',
            'export',
        ];

        foreach ($reportHints as $hint) {
            if (str_contains($normalized, $hint)) {
                return 'REPORT';
            }
        }

        $guideHints = [
            'huong dan',
            'giai dap',
            'cach dung',
            'su dung',
            'help',
            'faq',
        ];

        foreach ($guideHints as $hint) {
            if (str_contains($normalized, $hint)) {
                return 'GUIDE';
            }
        }

        return null;
    }

    public function isIssueRelatedMessage(string $message): bool
    {
        $normalized = $this->normalizeText($message);
        $hints = [
            'bao loi',
            'su co',
            'bug',
            'error',
            'khong vao duoc',
            'khong checkin duoc',
            'khong dang nhap duoc',
            'he thong loi',
            'khong in duoc',
            'treo',
            '500',
            '404',
        ];

        foreach ($hints as $hint) {
            if (str_contains($normalized, $hint)) {
                return true;
            }
        }

        return false;
    }

    public function getModeSelectionMarkdown(): string
    {
        return implode("\n", [
            '**Bạn muốn dùng chatbot theo chế độ nào?**',
            '1. `Hướng dẫn & giải đáp`: hỏi cách sử dụng, quy trình, hướng dẫn.',
            '2. `Xem báo cáo`: truy vấn số liệu sự kiện/khách hàng/check-in theo quyền.',
            '3. `Báo lỗi & hỗ trợ`: ghi nhận sự cố, gợi ý xử lý, tạo mã lỗi theo dõi.',
            '',
            'Bạn có thể bấm nút chọn nhanh ở dưới tin nhắn này.',
        ]);
    }

    public function getModeAckMarkdown(string $mode): string
    {
        if ($mode === 'GUIDE') {
            return implode("\n", [
                '**Đã chuyển sang chế độ Hướng dẫn & giải đáp.**',
                '- Chatbot sẽ ưu tiên giải đáp nghiệp vụ/hướng dẫn sử dụng.',
                '- Nếu bạn muốn xem báo cáo dữ liệu, bấm `R` để tạo chat mới hoặc chọn lại chế độ.',
            ]);
        }

        if ($mode === 'SUPPORT') {
            return implode("\n", [
                '**Đã chuyển sang chế độ Báo lỗi & hỗ trợ.**',
                '- Bạn có thể mô tả sự cố đang gặp và chatbot sẽ ghi nhận thành ticket.',
                '- Nếu cần, chatbot sẽ gợi ý bước xử lý nhanh theo mức độ ưu tiên.',
                '- Ví dụ: `Báo lỗi checkin không hoạt động ở event ABC từ 8h sáng`.',
            ]);
        }

        return implode("\n", [
            '**Đã chuyển sang chế độ Báo cáo dữ liệu.**',
            '- Chatbot chỉ truy xuất dữ liệu qua các hàm agent nội bộ (không truy cập DB trực tiếp từ AI).',
            '- Các nhóm xử lý: `Báo cáo nhanh`, `Thống kê`, `Báo cáo kèm biểu đồ`, `Xuất file`.',
            '',
            '**Ví dụ câu hỏi:**',
            '- `Hiện tại có bao nhiêu sự kiện đang chạy?`',
            '- `Có bao nhiêu khách hàng thuộc sự kiện <tên_sự_kiện>?`',
            '- `Thống kê sự kiện tháng trước và tháng kia`',
            '- `Làm báo cáo các sự kiện trong năm 2025`',
            '- `Xuất file báo cáo vừa rồi ra CSV`',
            '- `Xuất file báo cáo HTML có biểu đồ`',
        ]);
    }

    public function buildModeSelectionActions(): array
    {
        return [
            [
                'action' => 'set_mode',
                'mode' => 'GUIDE',
                'label' => 'Hướng dẫn & giải đáp',
            ],
            [
                'action' => 'set_mode',
                'mode' => 'REPORT',
                'label' => 'Xem báo cáo',
            ],
            [
                'action' => 'set_mode',
                'mode' => 'SUPPORT',
                'label' => 'Báo lỗi & hỗ trợ',
            ],
        ];
    }

    public function handleReportQuery(User $user, string $message): array
    {
        $this->ensureReportPermission($user);

        $request = $this->resolveReportRequest($user, $message);
        $intent = $request['intent'];

        $reply = match ($intent) {
            'running_events' => $this->buildRunningEventsQuickReport($user),
            'clients_by_event' => $this->buildClientsByEventQuickReport(
                $user,
                $message,
                $request['normalized'],
                (string) ($request['event_keyword'] ?? '')
            ),
            'event_overview' => $this->buildEventOverviewReport(
                $user,
                $message,
                $request['normalized'],
                (string) ($request['event_keyword'] ?? '')
            ),
            'top_events' => $this->buildTopEventsReport(
                $user,
                (int) ($request['year'] ?? now()->year)
            ),
            'monthly_statistics' => $this->buildMonthlyStatistics(
                $user,
                $request['months'] ?? [],
                (bool) ($request['include_revenue'] ?? false)
            ),
            'yearly_report' => $this->buildYearlyReportWithCharts(
                $user,
                (int) ($request['year'] ?? now()->year),
                (bool) ($request['include_revenue'] ?? false)
            ),
            'export_report' => $this->buildReportExport(
                $user,
                $request['memory_template'] ?? null,
                (string) ($request['export_format'] ?? 'csv')
            ),
            default => $this->buildUnknownIntentReply($request['memory_template'] ?? null),
        };

        $template = $this->buildTemplatePayload($intent, $request, $reply);
        $reply['template'] = $template;
        $reply['memory'] = [
            'used_previous_template' => (bool) ($request['used_previous_template'] ?? false),
            'followup_detected' => (bool) ($request['is_followup'] ?? false),
            'matched_from' => $request['matched_from'] ?? 'direct_intent',
        ];
        if (($request['used_previous_template'] ?? false) && $intent !== 'unknown') {
            $reply['markdown'] = implode("\n", [
                '> Đã áp dụng mẫu báo cáo gần nhất của bạn để giữ tính nhất quán.',
                '',
                (string) ($reply['markdown'] ?? ''),
            ]);
        }
        $reply['data'] = array_merge($reply['data'] ?? [], [
            'request_context' => [
                'normalized' => $request['normalized'] ?? '',
                'include_revenue' => (bool) ($request['include_revenue'] ?? false),
                'used_previous_template' => (bool) ($request['used_previous_template'] ?? false),
                'export_format' => (string) ($request['export_format'] ?? 'csv'),
            ],
        ]);
        $reply['actions'] = array_values(array_merge(
            (array) ($reply['actions'] ?? []),
            $this->defaultActionsForReport($intent)
        ));

        return $reply;
    }

    public function buildUserMemoryContext(User $user): array
    {
        $this->ensureReportPermission($user);

        $templates = $this->recentReportTemplates($user, 5);
        $lastTemplate = $templates[0] ?? null;

        return [
            'user_id' => (int) $user->id,
            'role_context' => [
                'is_system_admin' => $this->isSystemAdmin($user),
                'is_company_admin' => $this->isCompanyAdmin($user),
                'company_id' => (int) ($user->company_id ?? 0),
            ],
            'last_report_template' => $lastTemplate,
            'recent_report_templates' => $templates,
            'recent_issue_reports' => $this->recentIssueReports($user, 5),
            'consistency_rules' => [
                'reuse_last_template_when_followup' => true,
                'followup_keywords' => $this->followupHints(),
                'preserve_scope_by_permission' => true,
            ],
        ];
    }

    public function ensureReportPermission(User $user): void
    {
        if (! $user->hasRole(Role::ROLE_ADMIN)) {
            throw new AuthorizationException('Bạn không có quyền sử dụng chế độ báo cáo.');
        }
    }

    public function handleSupportQuery(User $user, string $message, ?int $sessionId = null): array
    {
        $this->ensureReportPermission($user);
        $normalized = $this->normalizeText($message);
        $intent = $this->detectIssueIntent($normalized, $message);

        return match ($intent) {
            'issue_status' => $this->buildIssueStatusReply($user, $message),
            'issue_summary' => $this->buildIssueSummaryReply($user, $normalized),
            'issue_create' => $this->buildIssueCreatedReply($user, $message, $normalized, $sessionId),
            default => $this->buildIssueGuideReply(),
        };
    }

    private function detectIssueIntent(string $normalized, string $originalMessage): string
    {
        if (
            str_contains($normalized, 'thong ke loi') ||
            str_contains($normalized, 'tong hop loi') ||
            str_contains($normalized, 'bao cao loi')
        ) {
            return 'issue_summary';
        }

        if (
            $this->extractIssueCode($originalMessage) !== null ||
            (
                (str_contains($normalized, 'ticket') || str_contains($normalized, 'ma loi')) &&
                (str_contains($normalized, 'trang thai') || str_contains($normalized, 'xu ly') || str_contains($normalized, 'ra sao'))
            )
        ) {
            return 'issue_status';
        }

        if ($this->isIssueRelatedMessage($normalized)) {
            return 'issue_create';
        }

        return 'issue_guide';
    }

    private function buildIssueCreatedReply(
        User $user,
        string $message,
        string $normalized,
        ?int $sessionId = null
    ): array {
        $severity = $this->detectIssueSeverity($normalized);
        $category = $this->detectIssueCategory($normalized);
        $eventKeyword = $this->extractEventKeyword($message, $normalized);
        $event = null;
        if ($eventKeyword !== '') {
            [$event] = $this->findEventByKeyword($user, $eventKeyword);
        }

        $companyId = $event ? (int) ($event->company_id ?? 0) : (int) ($user->company_id ?? 0);
        if ($companyId <= 0) {
            $companyId = null;
        }

        $suggestion = $this->buildIssueQuickSuggestion($normalized);
        $issue = N8nChatIssueReport::query()->create([
            'code' => Str::upper(Str::random(18)),
            'session_id' => $sessionId,
            'user_id' => $user->id,
            'company_id' => $companyId,
            'event_id' => $event ? (int) $event->id : null,
            'category' => $category,
            'severity' => $severity,
            'status' => N8nChatIssueReport::STATUS_OPEN,
            'title' => Str::limit(trim($message), 180),
            'description' => $message,
            'raw_user_message' => $message,
            'ai_suggestion' => $suggestion,
            'context' => [
                'event_keyword' => $eventKeyword ?: null,
                'detected_from' => 'chatbot_support_mode',
                'created_from_admin_chatbot' => true,
            ],
        ]);

        $issueCode = sprintf('INC-%s-%06d', now()->format('Ymd'), $issue->id);
        $issue->update([
            'code' => $issueCode,
        ]);

        $eventLabel = $event ? "[{$event->code}] {$event->name}" : 'Chưa xác định';
        $lines = [
            '**Đã ghi nhận lỗi/sự cố thành công.**',
            "- Mã theo dõi: **`{$issueCode}`**",
            "- Mức độ: **{$this->formatIssueSeverity($severity)}**",
            "- Nhóm lỗi: **{$this->formatIssueCategory($category)}**",
            "- Sự kiện liên quan: **{$eventLabel}**",
            '',
            '**Gợi ý xử lý nhanh:**',
            $suggestion,
            '',
            '> Bạn có thể hỏi tiếp: `Kiểm tra trạng thái ticket ' . $issueCode . '`',
        ];

        return [
            'category' => self::CATEGORY_ISSUE_SUPPORT,
            'intent' => 'issue_create',
            'markdown' => implode("\n", $lines),
            'charts' => [],
            'actions' => [
                [
                    'action' => 'copy_text',
                    'label' => 'Copy mã ticket',
                    'text' => $issueCode,
                ],
                [
                    'action' => 'send_preset',
                    'label' => 'Xem trạng thái ticket',
                    'message' => 'Kiểm tra trạng thái ticket ' . $issueCode,
                ],
            ],
            'data' => [
                'issue' => [
                    'id' => (int) $issue->id,
                    'code' => $issueCode,
                    'status' => $issue->status,
                    'severity' => $severity,
                    'category' => $category,
                    'event_id' => (int) ($issue->event_id ?? 0),
                    'event_label' => $eventLabel,
                ],
            ],
        ];
    }

    private function buildIssueStatusReply(User $user, string $message): array
    {
        $issueCode = $this->extractIssueCode($message);
        if ($issueCode === null) {
            return [
                'category' => self::CATEGORY_ISSUE_SUPPORT,
                'intent' => 'issue_status',
                'markdown' => implode("\n", [
                    '**Chưa xác định được mã ticket.**',
                    '- Vui lòng gửi theo mẫu: `Kiểm tra trạng thái ticket INC-YYYYMMDD-000001`',
                ]),
                'charts' => [],
                'data' => [],
            ];
        }

        $issue = $this->issueScopeForUser($user)
            ->whereRaw('UPPER(code) = ?', [Str::upper($issueCode)])
            ->first();

        if (! $issue) {
            return [
                'category' => self::CATEGORY_ISSUE_SUPPORT,
                'intent' => 'issue_status',
                'markdown' => implode("\n", [
                    '**Không tìm thấy ticket trong phạm vi quyền hiện tại.**',
                    "- Mã đã tra cứu: `{$issueCode}`",
                ]),
                'charts' => [],
                'data' => [
                    'issue_code' => $issueCode,
                ],
            ];
        }

        $lines = [
            "**Trạng thái ticket `{$issue->code}`**",
            '- Trạng thái: **' . $this->formatIssueStatus($issue->status) . '**',
            '- Mức độ: **' . $this->formatIssueSeverity((string) $issue->severity) . '**',
            '- Nhóm lỗi: **' . $this->formatIssueCategory((string) $issue->category) . '**',
            '- Tạo lúc: **' . optional($issue->created_at)->format('Y-m-d H:i:s') . '**',
            '- Cập nhật: **' . optional($issue->updated_at)->format('Y-m-d H:i:s') . '**',
        ];

        if (!empty($issue->ai_suggestion)) {
            $lines[] = '';
            $lines[] = '**Gợi ý xử lý đã lưu:**';
            $lines[] = (string) $issue->ai_suggestion;
        }

        return [
            'category' => self::CATEGORY_ISSUE_SUPPORT,
            'intent' => 'issue_status',
            'markdown' => implode("\n", $lines),
            'charts' => [],
            'actions' => [
                [
                    'action' => 'send_preset',
                    'label' => 'Thống kê lỗi mở',
                    'message' => 'Thống kê lỗi mở 30 ngày gần nhất',
                ],
            ],
            'data' => [
                'issue' => [
                    'id' => (int) $issue->id,
                    'code' => (string) $issue->code,
                    'status' => (string) $issue->status,
                    'severity' => (string) $issue->severity,
                    'category' => (string) $issue->category,
                    'event_id' => (int) ($issue->event_id ?? 0),
                ],
            ],
        ];
    }

    private function buildIssueSummaryReply(User $user, string $normalized): array
    {
        $query = $this->issueScopeForUser($user);
        $windowDays = 30;
        if (str_contains($normalized, '7 ngay')) {
            $windowDays = 7;
        } elseif (str_contains($normalized, 'hom nay')) {
            $windowDays = 1;
        }

        $fromDate = now()->subDays($windowDays - 1)->startOfDay();
        $query->where('created_at', '>=', $fromDate);

        $total = (clone $query)->count();
        $open = (clone $query)->where('status', N8nChatIssueReport::STATUS_OPEN)->count();
        $inProgress = (clone $query)->where('status', N8nChatIssueReport::STATUS_IN_PROGRESS)->count();
        $resolved = (clone $query)->whereIn('status', [
            N8nChatIssueReport::STATUS_RESOLVED,
            N8nChatIssueReport::STATUS_CLOSED,
        ])->count();

        $severityRows = (clone $query)
            ->selectRaw('severity, COUNT(*) as total')
            ->groupBy('severity')
            ->pluck('total', 'severity')
            ->toArray();

        $recent = (clone $query)
            ->orderByDesc('id')
            ->limit(8)
            ->get(['id', 'code', 'status', 'severity', 'category', 'created_at']);

        $lines = [
            "**Thống kê lỗi/sự cố {$windowDays} ngày gần nhất**",
            "- Tổng ticket: **{$total}**",
            "- Mở mới: **{$open}**",
            "- Đang xử lý: **{$inProgress}**",
            "- Đã xử lý/đóng: **{$resolved}**",
            '',
            '| Ticket | Trạng thái | Mức độ | Nhóm | Tạo lúc |',
            '|---|---|---|---|---|',
        ];

        foreach ($recent as $item) {
            $lines[] = '| ' . $item->code
                . ' | ' . $this->formatIssueStatus((string) $item->status)
                . ' | ' . $this->formatIssueSeverity((string) $item->severity)
                . ' | ' . $this->formatIssueCategory((string) $item->category)
                . ' | ' . optional($item->created_at)->format('Y-m-d H:i') . ' |';
        }

        $severityKeys = [
            N8nChatIssueReport::SEVERITY_LOW,
            N8nChatIssueReport::SEVERITY_MEDIUM,
            N8nChatIssueReport::SEVERITY_HIGH,
            N8nChatIssueReport::SEVERITY_CRITICAL,
        ];

        return [
            'category' => self::CATEGORY_ISSUE_SUPPORT,
            'intent' => 'issue_summary',
            'markdown' => implode("\n", $lines),
            'charts' => [
                [
                    'type' => 'donut',
                    'title' => 'Phân bổ mức độ lỗi',
                    'categories' => array_map(fn ($s) => $this->formatIssueSeverity($s), $severityKeys),
                    'series' => array_map(fn ($s) => (int) ($severityRows[$s] ?? 0), $severityKeys),
                    'height' => 280,
                ],
                [
                    'type' => 'bar',
                    'title' => 'Trạng thái ticket',
                    'categories' => ['Mở mới', 'Đang xử lý', 'Đã xử lý/đóng'],
                    'series' => [
                        [
                            'name' => 'Số lượng',
                            'data' => [$open, $inProgress, $resolved],
                        ],
                    ],
                    'height' => 280,
                ],
            ],
            'actions' => [
                [
                    'action' => 'send_preset',
                    'label' => 'Tạo ticket mới',
                    'message' => 'Báo lỗi checkin không quét được QR ở sự kiện ...',
                ],
            ],
            'data' => [
                'window_days' => $windowDays,
                'total' => $total,
                'open' => $open,
                'in_progress' => $inProgress,
                'resolved' => $resolved,
                'severity' => $severityRows,
            ],
        ];
    }

    private function buildIssueGuideReply(): array
    {
        return [
            'category' => self::CATEGORY_ISSUE_SUPPORT,
            'intent' => 'issue_guide',
            'markdown' => implode("\n", [
                '**Chế độ hỗ trợ lỗi/sự cố**',
                '- Bạn có thể gửi mô tả lỗi để tạo ticket ngay trong chatbot.',
                '- Ví dụ: `Báo lỗi không check-in được ở event ABC từ 09:00`.',
                '- Để kiểm tra trạng thái ticket: `Kiểm tra trạng thái ticket INC-YYYYMMDD-000001`.',
                '- Để xem tổng quan: `Thống kê lỗi mở 7 ngày gần nhất`.',
            ]),
            'charts' => [],
            'data' => [],
        ];
    }

    private function issueScopeForUser(User $user): Builder
    {
        $query = N8nChatIssueReport::query();
        if ($this->isSystemAdmin($user)) {
            return $query;
        }

        if ($this->isCompanyAdmin($user)) {
            $companyId = (int) ($user->company_id ?? 0);
            if ($companyId <= 0) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where('company_id', $companyId);
        }

        return $query->where('user_id', $user->id);
    }

    private function extractIssueCode(string $message): ?string
    {
        if (preg_match('/\\bINC-\\d{8}-\\d{4,8}\\b/i', $message, $matches)) {
            return Str::upper((string) $matches[0]);
        }

        return null;
    }

    private function detectIssueSeverity(string $normalized): string
    {
        if (
            str_contains($normalized, 'nghiem trong') ||
            str_contains($normalized, 'critical') ||
            str_contains($normalized, 'khong su dung duoc') ||
            str_contains($normalized, 'sap he thong')
        ) {
            return N8nChatIssueReport::SEVERITY_CRITICAL;
        }

        if (
            str_contains($normalized, 'gap') ||
            str_contains($normalized, 'khong checkin duoc') ||
            str_contains($normalized, 'khong dang nhap duoc') ||
            str_contains($normalized, 'khong in duoc')
        ) {
            return N8nChatIssueReport::SEVERITY_HIGH;
        }

        if (
            str_contains($normalized, 'cham') ||
            str_contains($normalized, 'thinh thoang') ||
            str_contains($normalized, 'khong on dinh')
        ) {
            return N8nChatIssueReport::SEVERITY_MEDIUM;
        }

        return N8nChatIssueReport::SEVERITY_LOW;
    }

    private function detectIssueCategory(string $normalized): string
    {
        if (str_contains($normalized, 'checkin') || str_contains($normalized, 'qr')) {
            return 'checkin';
        }

        if (str_contains($normalized, 'in') || str_contains($normalized, 'printer')) {
            return 'print';
        }

        if (str_contains($normalized, 'email') || str_contains($normalized, 'sms')) {
            return 'notification';
        }

        if (str_contains($normalized, 'dang nhap') || str_contains($normalized, 'login')) {
            return 'auth';
        }

        if (str_contains($normalized, 'bao cao') || str_contains($normalized, 'thong ke')) {
            return 'report';
        }

        return 'general';
    }

    private function buildIssueQuickSuggestion(string $normalized): string
    {
        if (str_contains($normalized, 'checkin') || str_contains($normalized, 'qr')) {
            return implode("\n", [
                '- Kiểm tra camera/quyền truy cập camera trên thiết bị check-in.',
                '- Kiểm tra mã QR có thuộc đúng sự kiện và còn hiệu lực.',
                '- Kiểm tra kết nối mạng và đồng bộ thời gian thiết bị.',
            ]);
        }

        if (str_contains($normalized, 'in') || str_contains($normalized, 'printer')) {
            return implode("\n", [
                '- Kiểm tra máy in đã online và đúng driver.',
                '- Kiểm tra mapping `print_device` với `label` và `printer` trong hệ thống.',
                '- In test 1 bản mẫu để xác nhận template và khổ giấy.',
            ]);
        }

        if (str_contains($normalized, 'dang nhap') || str_contains($normalized, 'login')) {
            return implode("\n", [
                '- Kiểm tra tài khoản còn hoạt động và role còn hợp lệ.',
                '- Kiểm tra cache/session có bị stale, thử đăng nhập lại.',
                '- Kiểm tra thời gian hệ thống máy trạm.',
            ]);
        }

        return implode("\n", [
            '- Xác nhận thời điểm bắt đầu xảy ra lỗi và phạm vi ảnh hưởng.',
            '- Kiểm tra log hệ thống gần nhất của module liên quan.',
            '- Thu thập ảnh chụp màn hình/video để tái hiện lỗi nhanh.',
        ]);
    }

    private function formatIssueSeverity(string $severity): string
    {
        return match (Str::lower($severity)) {
            N8nChatIssueReport::SEVERITY_CRITICAL => 'Critical',
            N8nChatIssueReport::SEVERITY_HIGH => 'High',
            N8nChatIssueReport::SEVERITY_MEDIUM => 'Medium',
            default => 'Low',
        };
    }

    private function formatIssueStatus(string $status): string
    {
        return match (Str::upper($status)) {
            N8nChatIssueReport::STATUS_OPEN => 'Open',
            N8nChatIssueReport::STATUS_IN_PROGRESS => 'In Progress',
            N8nChatIssueReport::STATUS_RESOLVED => 'Resolved',
            N8nChatIssueReport::STATUS_CLOSED => 'Closed',
            default => Str::upper($status),
        };
    }

    private function formatIssueCategory(string $category): string
    {
        return match (Str::lower($category)) {
            'checkin' => 'Check-in',
            'print' => 'In ấn',
            'notification' => 'Email/SMS',
            'auth' => 'Đăng nhập',
            'report' => 'Báo cáo',
            default => 'General',
        };
    }

    private function detectReportIntent(string $normalized): string
    {
        if (
            str_contains($normalized, 'xuat file') ||
            str_contains($normalized, 'export') ||
            str_contains($normalized, 'csv') ||
            str_contains($normalized, 'excel') ||
            str_contains($normalized, 'xlsx')
        ) {
            return 'export_report';
        }

        if (
            str_contains($normalized, 'chi tiet su kien') ||
            str_contains($normalized, 'tong quan su kien') ||
            str_contains($normalized, 'hieu suat su kien') ||
            (str_contains($normalized, 'su kien') && str_contains($normalized, 'ti le checkin'))
        ) {
            return 'event_overview';
        }

        if (
            str_contains($normalized, 'top su kien') ||
            str_contains($normalized, 'top event') ||
            str_contains($normalized, 'xep hang su kien')
        ) {
            return 'top_events';
        }

        if (str_contains($normalized, 'bao nhieu khach hang') && str_contains($normalized, 'su kien')) {
            return 'clients_by_event';
        }

        if (
            str_contains($normalized, 'su kien dang chay') ||
            str_contains($normalized, 'su kien dang dien ra') ||
            str_contains($normalized, 'bao nhieu su kien dang')
        ) {
            return 'running_events';
        }

        if (
            str_contains($normalized, 'thang truoc') ||
            str_contains($normalized, 'thang kia') ||
            preg_match('/\bthang\s+\d{1,2}(\s*\/\s*\d{4}|\s+nam\s+\d{4})?\b/', $normalized)
        ) {
            return 'monthly_statistics';
        }

        if (
            str_contains($normalized, 'bao cao') ||
            str_contains($normalized, 'tong hop')
        ) {
            if (preg_match('/\b20\d{2}\b/', $normalized)) {
                return 'yearly_report';
            }
        }

        if (str_contains($normalized, 'nam ') && str_contains($normalized, 'su kien')) {
            return 'yearly_report';
        }

        if (str_contains($normalized, 'thong ke') || str_contains($normalized, 'doanh thu')) {
            return 'monthly_statistics';
        }

        return 'unknown';
    }

    private function buildRunningEventsQuickReport(User $user): array
    {
        $today = now()->toDateString();

        $query = $this->eventScopeForUser($user)
            ->where('status', BaseModel::STATUS_ACTIVE)
            ->where(function (Builder $query) use ($today) {
                $query->where(function (Builder $q) use ($today) {
                    $q->whereNotNull('from_date')
                        ->whereNotNull('to_date')
                        ->whereDate('from_date', '<=', $today)
                        ->whereDate('to_date', '>=', $today);
                })->orWhere(function (Builder $q) use ($today) {
                    $q->whereNotNull('from_date')
                        ->whereNull('to_date')
                        ->whereDate('from_date', '<=', $today);
                })->orWhere(function (Builder $q) use ($today) {
                    $q->whereNull('from_date')
                        ->whereNotNull('to_date')
                        ->whereDate('to_date', '>=', $today);
                });
            });

        $count = (clone $query)->count();
        $events = (clone $query)
            ->orderBy('from_date')
            ->orderBy('id')
            ->limit(8)
            ->get(['id', 'code', 'name', 'from_date', 'to_date']);

        $lines = [
            '**Báo cáo nhanh: Sự kiện đang chạy**',
            "- Thời điểm ghi nhận: `{$today}`",
            "- Số sự kiện đang chạy: **{$count}**",
        ];

        if ($events->isNotEmpty()) {
            $lines[] = '';
            $lines[] = 'Danh sách mẫu:';

            foreach ($events as $index => $event) {
                $fromDate = $event->from_date ? Carbon::parse($event->from_date)->toDateString() : 'N/A';
                $toDate = $event->to_date ? Carbon::parse($event->to_date)->toDateString() : 'N/A';
                $lines[] = ($index + 1) . ". [{$event->code}] {$event->name} ({$fromDate} -> {$toDate})";
            }
        } else {
            $lines[] = '- Không có sự kiện nào đang chạy trong phạm vi quyền hiện tại.';
        }

        return [
            'category' => self::CATEGORY_QUICK_REPORT,
            'intent' => 'running_events',
            'markdown' => implode("\n", $lines),
            'charts' => [],
            'data' => [
                'at_date' => $today,
                'running_events_count' => $count,
                'event_samples' => $events->toArray(),
            ],
        ];
    }

    private function buildClientsByEventQuickReport(
        User $user,
        string $message,
        string $normalized,
        string $forcedKeyword = ''
    ): array
    {
        $keyword = trim($forcedKeyword) !== ''
            ? trim($forcedKeyword)
            : $this->extractEventKeyword($message, $normalized);

        if ($keyword === '') {
            return [
                'category' => self::CATEGORY_QUICK_REPORT,
                'intent' => 'clients_by_event',
                'markdown' => implode("\n", [
                    '**Báo cáo nhanh: Khách hàng theo sự kiện**',
                    '- Chưa xác định được tên/mã sự kiện.',
                    '- Ví dụ câu hỏi đúng: `Có bao nhiêu khách hàng thuộc sự kiện <tên_sự_kiện>?`',
                ]),
                'charts' => [],
                'data' => [],
            ];
        }

        [$event, $candidates] = $this->findEventByKeyword($user, $keyword);

        if (! $event) {
            $lines = [
                '**Báo cáo nhanh: Khách hàng theo sự kiện**',
                "- Không tìm thấy sự kiện phù hợp với từ khóa: `{$keyword}`.",
            ];

            if (count($candidates)) {
                $lines[] = '';
                $lines[] = 'Một số sự kiện gần nhất trong phạm vi quyền:';
                foreach ($candidates as $index => $candidate) {
                    $lines[] = ($index + 1) . ". [{$candidate->code}] {$candidate->name}";
                }
            }

            return [
                'category' => self::CATEGORY_QUICK_REPORT,
                'intent' => 'clients_by_event',
                'markdown' => implode("\n", $lines),
                'charts' => [],
                'data' => [
                    'keyword' => $keyword,
                ],
            ];
        }

        $totalClients = Client::query()
            ->where('event_id', $event->id)
            ->where('status', '!=', Client::STATUS_DELETED)
            ->count();

        $checkedInClients = Checkin::query()
            ->where('event_id', $event->id)
            ->where('type', Checkin::TYPE_CHECKIN)
            ->where('status', '!=', Checkin::STATUS_DELETED)
            ->distinct('qrcode')
            ->count('qrcode');

        $checkinRate = $totalClients > 0
            ? round(($checkedInClients / $totalClients) * 100, 2)
            : 0;

        $markdown = implode("\n", [
            '**Báo cáo nhanh: Khách hàng theo sự kiện**',
            "- Sự kiện: **[{$event->code}] {$event->name}**",
            "- Tổng số khách hàng: **{$totalClients}**",
            "- Số khách đã check-in: **{$checkedInClients}**",
            "- Tỉ lệ check-in: **{$checkinRate}%**",
        ]);

        return [
            'category' => self::CATEGORY_QUICK_REPORT,
            'intent' => 'clients_by_event',
            'markdown' => $markdown,
            'charts' => [
                [
                    'type' => 'donut',
                    'title' => "Tỉ lệ check-in - {$event->code}",
                    'categories' => ['Đã check-in', 'Chưa check-in'],
                    'series' => [
                        max($checkedInClients, 0),
                        max($totalClients - $checkedInClients, 0),
                    ],
                    'height' => 280,
                ],
            ],
            'data' => [
                'event' => [
                    'id' => $event->id,
                    'code' => $event->code,
                    'name' => $event->name,
                ],
                'total_clients' => $totalClients,
                'checked_in_clients' => $checkedInClients,
                'checkin_rate_percent' => $checkinRate,
            ],
        ];
    }

    private function buildEventOverviewReport(
        User $user,
        string $message,
        string $normalized,
        string $forcedKeyword = ''
    ): array {
        $keyword = trim($forcedKeyword) !== ''
            ? trim($forcedKeyword)
            : $this->extractEventKeyword($message, $normalized);

        if ($keyword === '') {
            return [
                'category' => self::CATEGORY_REPORT_WITH_CHART,
                'intent' => 'event_overview',
                'markdown' => implode("\n", [
                    '**Báo cáo chi tiết sự kiện**',
                    '- Chưa xác định được sự kiện mục tiêu.',
                    '- Ví dụ: `Báo cáo chi tiết sự kiện ABC`',
                ]),
                'charts' => [],
                'data' => [],
            ];
        }

        [$event] = $this->findEventByKeyword($user, $keyword);
        if (! $event) {
            return [
                'category' => self::CATEGORY_REPORT_WITH_CHART,
                'intent' => 'event_overview',
                'markdown' => implode("\n", [
                    '**Báo cáo chi tiết sự kiện**',
                    "- Không tìm thấy sự kiện theo từ khóa: `{$keyword}`",
                ]),
                'charts' => [],
                'data' => [
                    'keyword' => $keyword,
                ],
            ];
        }

        $totalClients = Client::query()
            ->where('event_id', $event->id)
            ->where('status', '!=', Client::STATUS_DELETED)
            ->count();

        $checkedInClients = Checkin::query()
            ->where('event_id', $event->id)
            ->where('type', Checkin::TYPE_CHECKIN)
            ->where('status', '!=', Checkin::STATUS_DELETED)
            ->distinct('qrcode')
            ->count('qrcode');

        $checkinRate = $totalClients > 0
            ? round(($checkedInClients / $totalClients) * 100, 2)
            : 0.0;

        $sourceRows = Client::query()
            ->where('event_id', $event->id)
            ->where('status', '!=', Client::STATUS_DELETED)
            ->selectRaw("COALESCE(register_source, 'UNKNOWN') as source, COUNT(*) as total")
            ->groupBy('source')
            ->orderByDesc('total')
            ->pluck('total', 'source')
            ->toArray();

        $startDate = Carbon::now()->subDays(13)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        if ($event->from_date && $event->to_date) {
            $eventStart = Carbon::parse($event->from_date)->startOfDay();
            $eventEnd = Carbon::parse($event->to_date)->endOfDay();
            if ($eventEnd->greaterThan($startDate)) {
                $startDate = $eventStart->greaterThan($startDate) ? $eventStart : $startDate;
                $endDate = $eventEnd->lessThan($endDate) ? $eventEnd : $endDate;
            }
        }

        $dailyRows = Checkin::query()
            ->where('event_id', $event->id)
            ->where('type', Checkin::TYPE_CHECKIN)
            ->where('status', '!=', Checkin::STATUS_DELETED)
            ->whereBetween('scan_time', [$startDate, $endDate])
            ->selectRaw('DATE(scan_time) as scan_date, COUNT(DISTINCT qrcode) as total')
            ->groupBy('scan_date')
            ->orderBy('scan_date')
            ->pluck('total', 'scan_date')
            ->toArray();

        $dailyLabels = [];
        $dailyValues = [];
        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            $dateKey = $cursor->toDateString();
            $dailyLabels[] = $cursor->format('d/m');
            $dailyValues[] = (int) ($dailyRows[$dateKey] ?? 0);
            $cursor->addDay();
        }

        $sourceLabelMap = Client::getRegisterSources();
        $sourceCategories = array_map(function ($source) use ($sourceLabelMap) {
            return $sourceLabelMap[$source] ?? $source;
        }, array_keys($sourceRows));
        $sourceValues = array_map(fn ($v) => (int) $v, array_values($sourceRows));

        $lines = [
            '**Báo cáo chi tiết sự kiện**',
            "- Sự kiện: **[{$event->code}] {$event->name}**",
            "- Tổng khách hàng: **{$totalClients}**",
            "- Đã check-in: **{$checkedInClients}**",
            "- Tỉ lệ check-in: **{$checkinRate}%**",
            "- Khoảng biểu đồ check-in: **{$startDate->toDateString()} -> {$endDate->toDateString()}**",
        ];

        return [
            'category' => self::CATEGORY_REPORT_WITH_CHART,
            'intent' => 'event_overview',
            'markdown' => implode("\n", $lines),
            'charts' => [
                [
                    'type' => 'line',
                    'title' => "Check-in theo ngày - {$event->code}",
                    'categories' => $dailyLabels,
                    'series' => [
                        [
                            'name' => 'Check-in',
                            'data' => $dailyValues,
                        ],
                    ],
                    'height' => 280,
                ],
                [
                    'type' => 'donut',
                    'title' => 'Nguồn đăng ký khách hàng',
                    'categories' => $sourceCategories,
                    'series' => $sourceValues,
                    'height' => 280,
                ],
            ],
            'data' => [
                'event' => [
                    'id' => (int) $event->id,
                    'code' => (string) $event->code,
                    'name' => (string) $event->name,
                ],
                'keyword' => $keyword,
                'total_clients' => $totalClients,
                'checked_in_clients' => $checkedInClients,
                'checkin_rate_percent' => $checkinRate,
                'daily_checkins' => array_map(function ($label, $value) {
                    return [
                        'date_label' => $label,
                        'checkins' => (int) $value,
                    ];
                }, $dailyLabels, $dailyValues),
                'sources' => array_map(function ($source, $count) use ($sourceLabelMap) {
                    return [
                        'source' => $source,
                        'source_label' => $sourceLabelMap[$source] ?? $source,
                        'total' => (int) $count,
                    ];
                }, array_keys($sourceRows), array_values($sourceRows)),
            ],
        ];
    }

    private function buildTopEventsReport(User $user, int $year): array
    {
        if ($year <= 0) {
            $year = (int) now()->year;
        }

        $events = $this->eventScopeForUser($user)
            ->whereYear('from_date', $year)
            ->get(['id', 'code', 'name']);

        if ($events->isEmpty()) {
            return [
                'category' => self::CATEGORY_STATISTICS,
                'intent' => 'top_events',
                'markdown' => "**Không có dữ liệu sự kiện cho năm {$year}.**",
                'charts' => [],
                'data' => [
                    'year' => $year,
                    'rows' => [],
                ],
            ];
        }

        $eventIds = $events->pluck('id')->all();

        $clientsMap = Client::query()
            ->whereIn('event_id', $eventIds)
            ->where('status', '!=', Client::STATUS_DELETED)
            ->selectRaw('event_id, COUNT(*) as total')
            ->groupBy('event_id')
            ->pluck('total', 'event_id');

        $checkinsMap = Checkin::query()
            ->whereIn('event_id', $eventIds)
            ->where('type', Checkin::TYPE_CHECKIN)
            ->where('status', '!=', Checkin::STATUS_DELETED)
            ->selectRaw('event_id, COUNT(DISTINCT qrcode) as total')
            ->groupBy('event_id')
            ->pluck('total', 'event_id');

        $rows = $events->map(function (Event $event) use ($clientsMap, $checkinsMap) {
            $totalClients = (int) ($clientsMap[$event->id] ?? 0);
            $totalCheckins = (int) ($checkinsMap[$event->id] ?? 0);
            $rate = $totalClients > 0
                ? round(($totalCheckins / $totalClients) * 100, 2)
                : 0.0;

            return [
                'event_id' => (int) $event->id,
                'event_code' => (string) $event->code,
                'event_name' => (string) $event->name,
                'total_clients' => $totalClients,
                'total_checkins' => $totalCheckins,
                'checkin_rate_percent' => $rate,
            ];
        })->sortByDesc('total_checkins')
            ->values()
            ->take(10)
            ->all();

        $lines = [
            "**Top sự kiện theo check-in năm {$year}**",
            '',
            '| # | Mã | Tên sự kiện | Khách hàng | Check-in | Tỉ lệ |',
            '|---:|---|---|---:|---:|---:|',
        ];
        foreach ($rows as $index => $row) {
            $lines[] = '| ' . ($index + 1) . " | {$row['event_code']} | {$row['event_name']} | {$row['total_clients']} | {$row['total_checkins']} | {$row['checkin_rate_percent']}% |";
        }

        return [
            'category' => self::CATEGORY_STATISTICS,
            'intent' => 'top_events',
            'markdown' => implode("\n", $lines),
            'charts' => [
                [
                    'type' => 'bar',
                    'title' => "Top event theo check-in - {$year}",
                    'categories' => array_map(fn ($row) => $row['event_code'], $rows),
                    'series' => [
                        [
                            'name' => 'Khách hàng',
                            'data' => array_map(fn ($row) => $row['total_clients'], $rows),
                        ],
                        [
                            'name' => 'Check-in',
                            'data' => array_map(fn ($row) => $row['total_checkins'], $rows),
                        ],
                    ],
                    'height' => 320,
                ],
            ],
            'data' => [
                'year' => $year,
                'rows' => $rows,
            ],
        ];
    }

    private function buildReportExport(User $user, ?array $memoryTemplate = null, string $format = 'csv'): array
    {
        if (!is_array($memoryTemplate) || empty($memoryTemplate['intent'])) {
            return [
                'category' => self::CATEGORY_EXPORT,
                'intent' => 'export_report',
                'markdown' => implode("\n", [
                    '**Chưa có mẫu báo cáo gần nhất để xuất file.**',
                    '- Hãy yêu cầu tạo một báo cáo trước, sau đó nhắn: `Xuất file báo cáo vừa rồi`.',
                ]),
                'charts' => [],
                'data' => [],
            ];
        }

        $format = Str::lower(trim($format));
        if (!in_array($format, ['csv', 'html', 'xlsx', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }
        if (in_array($format, ['xlsx', 'excel'], true)) {
            $format = 'csv';
        }
        if ($format === 'pdf') {
            $format = 'html';
        }

        $intent = (string) $memoryTemplate['intent'];
        $reportSnapshot = $this->rebuildReportFromTemplate($user, $memoryTemplate);
        if (!$reportSnapshot) {
            return [
                'category' => self::CATEGORY_EXPORT,
                'intent' => 'export_report',
                'markdown' => implode("\n", [
                    '**Không thể dựng lại dữ liệu từ mẫu báo cáo trước đó.**',
                    '- Vui lòng tạo báo cáo mới rồi thử xuất file lại.',
                ]),
                'charts' => [],
                'data' => [
                    'template_intent' => $intent,
                ],
            ];
        }

        $rows = $this->buildExportRows($intent, (array) ($reportSnapshot['data'] ?? []));
        if (!count($rows)) {
            return [
                'category' => self::CATEGORY_EXPORT,
                'intent' => 'export_report',
                'markdown' => '**Dữ liệu báo cáo hiện tại chưa đủ để xuất file CSV.**',
                'charts' => [],
                'data' => [
                    'template_intent' => $intent,
                ],
            ];
        }

        $export = $format === 'html'
            ? $this->writeHtmlExport($user, $intent, $reportSnapshot, $rows)
            : $this->writeCsvExport($user, $rows, $intent);
        $formatLabel = Str::upper($format);

        return [
            'category' => self::CATEGORY_EXPORT,
            'intent' => 'export_report',
            'markdown' => implode("\n", [
                '**Đã tạo file báo cáo thành công.**',
                "- Mẫu báo cáo: `{$intent}`",
                "- Định dạng: `{$formatLabel}`",
                "- Tải file: {$export['url']}",
            ]),
            'charts' => [],
            'actions' => [
                [
                    'action' => 'open_url',
                    'label' => 'Tải file',
                    'url' => $export['url'],
                ],
                [
                    'action' => 'copy_text',
                    'label' => 'Copy link tải file',
                    'text' => $export['url'],
                ],
            ],
            'data' => [
                'template_intent' => $intent,
                'format' => $format,
                'file_name' => $export['file_name'],
                'file_path' => $export['path'],
                'file_url' => $export['url'],
                'row_count' => count($rows),
            ],
        ];
    }

    private function buildMonthlyStatistics(User $user, array $months = [], bool $includeRevenue = false): array
    {
        if (!count($months)) {
            $months[] = now()->startOfMonth();
        }

        $rows = [];
        foreach ($months as $month) {
            $rows[] = $this->getMonthlySnapshot($user, $month, $includeRevenue);
        }

        $markdownLines = [
            '**Thống kê sự kiện theo tháng**',
            '',
            $includeRevenue
                ? '| Tháng | Sự kiện | Đang hoạt động | Hoàn tất | Khách hàng | Khách check-in | Doanh thu |'
                : '| Tháng | Sự kiện | Đang hoạt động | Hoàn tất | Khách hàng | Khách check-in |',
            $includeRevenue
                ? '|---|---:|---:|---:|---:|---:|---:|'
                : '|---|---:|---:|---:|---:|---:|',
        ];

        foreach ($rows as $row) {
            if ($includeRevenue) {
                $markdownLines[] = "| {$row['label']} | {$row['total_events']} | {$row['active_events']} | {$row['done_events']} | {$row['total_clients']} | {$row['checkedin_clients']} | {$this->formatMoney($row['revenue'])} |";
            } else {
                $markdownLines[] = "| {$row['label']} | {$row['total_events']} | {$row['active_events']} | {$row['done_events']} | {$row['total_clients']} | {$row['checkedin_clients']} |";
            }
        }

        $charts = [
            [
                'type' => 'bar',
                'title' => $includeRevenue
                    ? 'Số lượng sự kiện, khách hàng và doanh thu theo tháng'
                    : 'Số lượng sự kiện và khách hàng theo tháng',
                'categories' => array_column($rows, 'label'),
                'series' => [
                    [
                        'name' => 'Sự kiện',
                        'data' => array_column($rows, 'total_events'),
                    ],
                    [
                        'name' => 'Khách hàng',
                        'data' => array_column($rows, 'total_clients'),
                    ],
                    [
                        'name' => 'Check-in',
                        'data' => array_column($rows, 'checkedin_clients'),
                    ],
                ],
                'height' => 320,
            ],
        ];

        if ($includeRevenue) {
            $charts[0]['series'][] = [
                'name' => 'Doanh thu',
                'data' => array_map(fn ($value) => (float) $value, array_column($rows, 'revenue')),
            ];
        }

        $totalRevenue = array_sum(array_column($rows, 'revenue'));
        if ($includeRevenue && $totalRevenue <= 0) {
            $markdownLines[] = '';
            $markdownLines[] = '> Lưu ý: Chưa có dữ liệu doanh thu hoặc bảng `orders` chưa sẵn sàng.';
        }

        return [
            'category' => self::CATEGORY_STATISTICS,
            'intent' => 'monthly_statistics',
            'markdown' => implode("\n", $markdownLines),
            'charts' => $charts,
            'data' => [
                'months' => $rows,
                'include_revenue' => $includeRevenue,
                'total_revenue' => $totalRevenue,
            ],
        ];
    }

    private function buildYearlyReportWithCharts(User $user, int $year, bool $includeRevenue = false): array
    {
        if ($year <= 0) {
            $year = now()->year;
        }

        $eventQuery = $this->eventScopeForUser($user)
            ->whereNotNull('from_date')
            ->whereYear('from_date', $year);

        $eventIds = (clone $eventQuery)->pluck('id');
        $totalEvents = $eventIds->count();

        $eventByMonth = array_fill(1, 12, 0);
        $clientByMonth = array_fill(1, 12, 0);
        $checkinByMonth = array_fill(1, 12, 0);
        $revenueByMonth = array_fill(1, 12, 0.0);

        $eventRows = (clone $eventQuery)
            ->selectRaw('MONTH(from_date) as month_num, COUNT(*) as total')
            ->groupBy('month_num')
            ->pluck('total', 'month_num');

        foreach ($eventRows as $monthNum => $total) {
            $eventByMonth[(int) $monthNum] = (int) $total;
        }

        if ($eventIds->isNotEmpty()) {
            $clientRows = Client::query()
                ->join('events', 'events.id', '=', 'clients.event_id')
                ->whereIn('events.id', $eventIds)
                ->where('clients.status', '!=', Client::STATUS_DELETED)
                ->selectRaw('MONTH(events.from_date) as month_num, COUNT(clients.id) as total')
                ->groupBy('month_num')
                ->pluck('total', 'month_num');

            foreach ($clientRows as $monthNum => $total) {
                $clientByMonth[(int) $monthNum] = (int) $total;
            }

            $checkinRows = Checkin::query()
                ->whereIn('event_id', $eventIds)
                ->where('type', Checkin::TYPE_CHECKIN)
                ->where('status', '!=', Checkin::STATUS_DELETED)
                ->whereYear('scan_time', $year)
                ->selectRaw('MONTH(scan_time) as month_num, COUNT(DISTINCT qrcode) as total')
                ->groupBy('month_num')
                ->pluck('total', 'month_num');

            foreach ($checkinRows as $monthNum => $total) {
                $checkinByMonth[(int) $monthNum] = (int) $total;
            }
        }

        $totalClients = array_sum($clientByMonth);
        $totalCheckins = array_sum($checkinByMonth);
        if ($includeRevenue) {
            $revenueRows = $this->resolveRevenueByMonth($eventIds, $year);
            foreach ($revenueRows as $monthNum => $total) {
                $monthInt = (int) $monthNum;
                if ($monthInt < 1 || $monthInt > 12) {
                    continue;
                }
                $revenueByMonth[$monthInt] = (float) $total;
            }
        }
        $totalRevenue = array_sum($revenueByMonth);

        $topEvents = collect();
        if ($eventIds->isNotEmpty()) {
            $topEvents = Client::query()
                ->join('events', 'events.id', '=', 'clients.event_id')
                ->whereIn('events.id', $eventIds)
                ->where('clients.status', '!=', Client::STATUS_DELETED)
                ->groupBy('events.id', 'events.code', 'events.name')
                ->select([
                    'events.id',
                    'events.code',
                    'events.name',
                    DB::raw('COUNT(clients.id) as total_clients'),
                ])
                ->orderByDesc('total_clients')
                ->limit(10)
                ->get();
        }

        $monthLabels = collect(range(1, 12))
            ->map(fn ($month) => "Thg {$month}")
            ->all();

        $markdownLines = [
            "**Báo cáo năm {$year}: Tổng hợp sự kiện**",
            "- Tổng sự kiện: **{$totalEvents}**",
            "- Tổng khách hàng: **{$totalClients}**",
            "- Tổng check-in (lọc trùng theo tháng): **{$totalCheckins}**",
        ];
        if ($includeRevenue) {
            $markdownLines[] = "- Tổng doanh thu: **{$this->formatMoney($totalRevenue)}**";
        }
        $markdownLines[] = '';

        if ($topEvents->isNotEmpty()) {
            $markdownLines[] = '| # | Mã | Tên sự kiện | Khách hàng |';
            $markdownLines[] = '|---:|---|---|---:|';

            foreach ($topEvents as $index => $event) {
                $markdownLines[] = '| ' . ($index + 1) . " | {$event->code} | {$event->name} | {$event->total_clients} |";
            }
        } else {
            $markdownLines[] = 'Không có dữ liệu sự kiện trong năm đã yêu cầu.';
        }

        $charts = [
            [
                'type' => 'line',
                'title' => "Xu hướng theo tháng năm {$year}",
                'categories' => $monthLabels,
                'series' => [
                    [
                        'name' => 'Sự kiện',
                        'data' => array_values($eventByMonth),
                    ],
                    [
                        'name' => 'Khách hàng',
                        'data' => array_values($clientByMonth),
                    ],
                    [
                        'name' => 'Check-in',
                        'data' => array_values($checkinByMonth),
                    ],
                ],
                'height' => 320,
            ],
        ];
        if ($includeRevenue) {
            $charts[0]['series'][] = [
                'name' => 'Doanh thu',
                'data' => array_values($revenueByMonth),
            ];
        }

        if ($topEvents->isNotEmpty()) {
            $charts[] = [
                'type' => 'bar',
                'title' => 'Top 10 sự kiện theo số khách hàng',
                'categories' => $topEvents->pluck('code')->all(),
                'series' => [
                    [
                        'name' => 'Khách hàng',
                        'data' => $topEvents->pluck('total_clients')->map(fn ($v) => (int) $v)->all(),
                    ],
                ],
                'height' => 320,
            ];
        }

        return [
            'category' => self::CATEGORY_REPORT_WITH_CHART,
            'intent' => 'yearly_report',
            'markdown' => implode("\n", $markdownLines),
            'charts' => $charts,
            'data' => [
                'year' => $year,
                'total_events' => $totalEvents,
                'total_clients' => $totalClients,
                'total_checkins' => $totalCheckins,
                'include_revenue' => $includeRevenue,
                'total_revenue' => $totalRevenue,
                'events_by_month' => array_values($eventByMonth),
                'clients_by_month' => array_values($clientByMonth),
                'checkins_by_month' => array_values($checkinByMonth),
                'revenue_by_month' => array_values($revenueByMonth),
                'top_events' => $topEvents->toArray(),
            ],
        ];
    }

    private function buildUnknownIntentReply(?array $memoryTemplate = null): array
    {
        $lines = [
            '**Chưa nhận dạng được mẫu báo cáo phù hợp.**',
            'Bạn có thể thử 1 trong các mẫu sau:',
            '- `Hiện tại có bao nhiêu sự kiện đang chạy?`',
            '- `Có bao nhiêu khách hàng thuộc sự kiện <tên_sự_kiện>?`',
            '- `Báo cáo chi tiết sự kiện <tên_sự_kiện>`',
            '- `Thống kê sự kiện tháng trước và tháng kia`',
            '- `Làm báo cáo các sự kiện trong năm 2025`',
            '- `Top sự kiện theo check-in năm 2025`',
            '- `Xuất file báo cáo vừa rồi ra CSV`',
        ];
        if ($memoryTemplate) {
            $lines[] = '';
            $lines[] = '**Mẫu báo cáo gần nhất của bạn:**';
            $lines[] = "- Intent: `" . ($memoryTemplate['intent'] ?? 'unknown') . '`';
            if (!empty($memoryTemplate['year'])) {
                $lines[] = "- Năm: `{$memoryTemplate['year']}`";
            }
            if (!empty($memoryTemplate['event_label'])) {
                $lines[] = "- Sự kiện: `{$memoryTemplate['event_label']}`";
            }
            $lines[] = '- Bạn có thể hỏi: `Cho tôi báo cáo tương tự hôm trước nhưng thêm doanh thu`';
        }
        $markdown = implode("\n", $lines);

        return [
            'category' => self::CATEGORY_QUICK_REPORT,
            'intent' => 'unknown',
            'markdown' => $markdown,
            'charts' => [],
            'data' => [],
        ];
    }

    private function defaultActionsForReport(string $intent): array
    {
        if (in_array($intent, ['unknown', 'export_report'], true)) {
            return [];
        }

        return [
            [
                'action' => 'send_preset',
                'label' => 'Xuất CSV báo cáo này',
                'message' => 'Xuất file báo cáo vừa rồi ra CSV',
            ],
            [
                'action' => 'send_preset',
                'label' => 'Xuất file có biểu đồ',
                'message' => 'Xuất file báo cáo HTML có biểu đồ minh hoạ',
            ],
            [
                'action' => 'send_preset',
                'label' => 'Thêm doanh thu',
                'message' => 'Cho tôi báo cáo tương tự nhưng thêm doanh thu',
            ],
        ];
    }

    private function getMonthlySnapshot(User $user, Carbon $month, bool $includeRevenue = false): array
    {
        $fromDate = $month->copy()->startOfMonth();
        $toDate = $month->copy()->endOfMonth();

        $eventsInMonth = $this->eventScopeForUser($user)
            ->where(function (Builder $query) use ($fromDate, $toDate) {
                $query->where(function (Builder $q) use ($fromDate, $toDate) {
                    $q->whereNotNull('from_date')
                        ->whereNotNull('to_date')
                        ->whereDate('from_date', '<=', $toDate->toDateString())
                        ->whereDate('to_date', '>=', $fromDate->toDateString());
                })->orWhere(function (Builder $q) use ($fromDate, $toDate) {
                    $q->whereNull('from_date')
                        ->whereNotNull('to_date')
                        ->whereDate('to_date', '>=', $fromDate->toDateString())
                        ->whereDate('to_date', '<=', $toDate->toDateString());
                })->orWhere(function (Builder $q) use ($fromDate, $toDate) {
                    $q->whereNotNull('from_date')
                        ->whereNull('to_date')
                        ->whereDate('from_date', '>=', $fromDate->toDateString())
                        ->whereDate('from_date', '<=', $toDate->toDateString());
                });
            });

        $totalEvents = (clone $eventsInMonth)->count();
        $activeEvents = (clone $eventsInMonth)->where('status', BaseModel::STATUS_ACTIVE)->count();
        $doneEvents = (clone $eventsInMonth)->where('status', Event::STATUS_DONE)->count();
        $eventIds = (clone $eventsInMonth)->pluck('id');

        $totalClients = 0;
        $checkedinClients = 0;
        $revenue = 0.0;
        if ($eventIds->isNotEmpty()) {
            $totalClients = Client::query()
                ->whereIn('event_id', $eventIds)
                ->where('status', '!=', Client::STATUS_DELETED)
                ->count();

            $checkedinClients = Checkin::query()
                ->whereIn('event_id', $eventIds)
                ->where('type', Checkin::TYPE_CHECKIN)
                ->where('status', '!=', Checkin::STATUS_DELETED)
                ->distinct('qrcode')
                ->count('qrcode');

            if ($includeRevenue) {
                $revenueRows = $this->resolveRevenueByMonth($eventIds, (int) $month->year);
                $revenue = (float) ($revenueRows[$month->month] ?? 0);
            }
        }

        return [
            'month' => $month->month,
            'year' => $month->year,
            'label' => $month->format('m/Y'),
            'total_events' => $totalEvents,
            'active_events' => $activeEvents,
            'done_events' => $doneEvents,
            'total_clients' => $totalClients,
            'checkedin_clients' => $checkedinClients,
            'revenue' => $revenue,
        ];
    }

    private function resolveMonthTargets(string $normalized): array
    {
        $targets = [];
        $now = now();

        if (str_contains($normalized, 'thang nay')) {
            $targets[] = $now->copy()->startOfMonth();
        }

        if (str_contains($normalized, 'thang truoc')) {
            $targets[] = $now->copy()->subMonthNoOverflow()->startOfMonth();
        }

        if (str_contains($normalized, 'thang kia')) {
            $targets[] = $now->copy()->subMonthsNoOverflow(2)->startOfMonth();
        }

        if (preg_match_all('/\bthang\s+(\d{1,2})(?:\s*(?:\/|-)\s*(\d{4})|\s+nam\s+(20\d{2}))?\b/', $normalized, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $month = (int) ($match[1] ?? 0);
                if ($month < 1 || $month > 12) {
                    continue;
                }

                $year = (int) ($match[2] ?? ($match[3] ?? $now->year));
                $targets[] = Carbon::create($year, $month, 1)->startOfMonth();
            }
        }

        $unique = [];
        foreach ($targets as $target) {
            $key = $target->format('Y-m');
            $unique[$key] = $target;
        }

        ksort($unique);

        return array_values($unique);
    }

    private function extractYear(string $normalized): ?int
    {
        if (preg_match('/\b(20\d{2})\b/', $normalized, $match)) {
            return (int) $match[1];
        }

        return null;
    }

    private function resolveReportRequest(User $user, string $message): array
    {
        $normalized = $this->normalizeText($message);
        $intent = $this->detectReportIntent($normalized);
        $memoryTemplate = $this->findLatestReportTemplate($user);
        $isFollowup = $this->isFollowupRequest($normalized);

        $eventKeyword = $this->extractEventKeyword($message, $normalized);
        $months = $this->resolveMonthTargets($normalized);
        $year = $this->extractYear($normalized);
        $includeRevenue = $this->containsRevenueHint($normalized);
        $exportFormat = $this->extractExportFormat($normalized);

        $usedPreviousTemplate = false;
        $matchedFrom = $intent !== 'unknown' ? 'direct_intent' : 'unknown';

        if ($isFollowup && $memoryTemplate) {
            if ($intent === 'unknown' && !empty($memoryTemplate['intent'])) {
                $intent = (string) $memoryTemplate['intent'];
                $usedPreviousTemplate = true;
                $matchedFrom = 'previous_template_intent';
            }

            if ($eventKeyword === '' && !empty($memoryTemplate['event_keyword'])) {
                $eventKeyword = (string) $memoryTemplate['event_keyword'];
                $usedPreviousTemplate = true;
            }

            if (!count($months)) {
                $months = $this->resolveMonthsFromTemplate($memoryTemplate);
                if (count($months)) {
                    $usedPreviousTemplate = true;
                }
            }

            if (($year === null || $year <= 0) && !empty($memoryTemplate['year'])) {
                $year = (int) $memoryTemplate['year'];
                $usedPreviousTemplate = true;
            }

            if (! $includeRevenue && !empty($memoryTemplate['include_revenue'])) {
                $includeRevenue = true;
                $usedPreviousTemplate = true;
            }
        }

        if ($includeRevenue && $matchedFrom === 'unknown') {
            $matchedFrom = 'revenue_followup';
        }

        return [
            'normalized' => $normalized,
            'intent' => $intent,
            'event_keyword' => $eventKeyword,
            'months' => $months,
            'year' => $year ?? now()->year,
            'include_revenue' => $includeRevenue,
            'export_format' => $exportFormat,
            'is_followup' => $isFollowup,
            'used_previous_template' => $usedPreviousTemplate,
            'matched_from' => $matchedFrom,
            'memory_template' => $memoryTemplate,
        ];
    }

    private function buildTemplatePayload(string $intent, array $request, array $reply): array
    {
        $data = (array) ($reply['data'] ?? []);
        $template = [
            'intent' => $intent,
            'category' => $reply['category'] ?? self::CATEGORY_QUICK_REPORT,
            'include_revenue' => (bool) ($request['include_revenue'] ?? false),
            'export_format' => (string) ($request['export_format'] ?? 'csv'),
            'year' => null,
            'month_keys' => [],
            'event_keyword' => null,
            'event_label' => null,
        ];

        if (in_array($intent, ['clients_by_event', 'event_overview'], true)) {
            $eventCode = data_get($data, 'event.code');
            $eventName = data_get($data, 'event.name');
            $template['event_keyword'] = $request['event_keyword'] ?: data_get($data, 'keyword');
            $template['event_label'] = trim(
                (($eventCode ? "[{$eventCode}] " : '') . (string) ($eventName ?? ''))
            ) ?: null;
        }

        if ($intent === 'monthly_statistics') {
            $rows = collect((array) data_get($data, 'months', []));
            $template['month_keys'] = $rows
                ->map(function ($row) {
                    $year = (int) data_get($row, 'year', 0);
                    $month = (int) data_get($row, 'month', 0);
                    if ($year <= 0 || $month < 1 || $month > 12) {
                        return null;
                    }
                    return sprintf('%04d-%02d', $year, $month);
                })
                ->filter()
                ->values()
                ->all();
        }

        if (in_array($intent, ['yearly_report', 'top_events'], true)) {
            $template['year'] = (int) (data_get($data, 'year') ?: ($request['year'] ?? now()->year));
            $template['include_revenue'] = (bool) (data_get($data, 'include_revenue') || $template['include_revenue']);
        }

        return $template;
    }

    private function recentReportTemplates(User $user, int $limit = 5): array
    {
        $messages = N8nChatMessage::query()
            ->where('role', N8nChatMessage::ROLE_ASSISTANT)
            ->whereHas('session', function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderByDesc('id')
            ->limit(200)
            ->get(['id', 'meta', 'created_at']);

        $templates = [];
        $seenHashes = [];
        foreach ($messages as $message) {
            $meta = is_array($message->meta) ? $message->meta : [];
            if (data_get($meta, 'kind') !== 'report') {
                continue;
            }

            $template = data_get($meta, 'template');
            if (!is_array($template)) {
                $template = [
                    'intent' => data_get($meta, 'intent', 'unknown'),
                    'category' => data_get($meta, 'category', self::CATEGORY_QUICK_REPORT),
                    'include_revenue' => (bool) data_get($meta, 'data.include_revenue', false),
                    'year' => data_get($meta, 'data.year'),
                    'event_keyword' => data_get($meta, 'data.keyword'),
                    'event_label' => trim(
                        ((data_get($meta, 'data.event.code') ? '[' . data_get($meta, 'data.event.code') . '] ' : '') .
                        (string) (data_get($meta, 'data.event.name') ?? ''))
                    ) ?: null,
                    'month_keys' => collect((array) data_get($meta, 'data.months', []))
                        ->map(function ($row) {
                            $year = (int) data_get($row, 'year', 0);
                            $month = (int) data_get($row, 'month', 0);
                            if ($year <= 0 || $month < 1 || $month > 12) {
                                return null;
                            }
                            return sprintf('%04d-%02d', $year, $month);
                        })
                        ->filter()
                        ->values()
                        ->all(),
                ];
            }

            if (($template['intent'] ?? null) === 'export_report') {
                continue;
            }

            $template['user_query'] = (string) data_get($meta, 'user_query', '');
            $template['source_message_id'] = (int) $message->id;
            $template['generated_at'] = optional($message->created_at)->toIso8601String();

            $hash = md5(json_encode([
                'intent' => $template['intent'] ?? 'unknown',
                'year' => $template['year'] ?? null,
                'event_keyword' => $template['event_keyword'] ?? null,
                'month_keys' => $template['month_keys'] ?? [],
                'include_revenue' => (bool) ($template['include_revenue'] ?? false),
            ]));

            if (isset($seenHashes[$hash])) {
                continue;
            }
            $seenHashes[$hash] = true;
            $templates[] = $template;

            if (count($templates) >= $limit) {
                break;
            }
        }

        return $templates;
    }

    private function recentIssueReports(User $user, int $limit = 5): array
    {
        return $this->issueScopeForUser($user)
            ->orderByDesc('id')
            ->limit(max(1, min($limit, 20)))
            ->get(['id', 'code', 'status', 'severity', 'category', 'event_id', 'created_at'])
            ->map(function (N8nChatIssueReport $issue) {
                return [
                    'id' => (int) $issue->id,
                    'code' => (string) $issue->code,
                    'status' => (string) $issue->status,
                    'severity' => (string) $issue->severity,
                    'category' => (string) $issue->category,
                    'event_id' => (int) ($issue->event_id ?? 0),
                    'created_at' => optional($issue->created_at)->toIso8601String(),
                ];
            })
            ->values()
            ->all();
    }

    private function findLatestReportTemplate(User $user): ?array
    {
        $templates = $this->recentReportTemplates($user, 1);

        return $templates[0] ?? null;
    }

    private function resolveMonthsFromTemplate(array $template): array
    {
        $targets = [];
        $monthKeys = array_values((array) ($template['month_keys'] ?? []));
        foreach ($monthKeys as $monthKey) {
            if (!is_string($monthKey) || !preg_match('/^\d{4}-\d{2}$/', $monthKey)) {
                continue;
            }

            [$year, $month] = explode('-', $monthKey);
            $yearInt = (int) $year;
            $monthInt = (int) $month;
            if ($yearInt <= 0 || $monthInt < 1 || $monthInt > 12) {
                continue;
            }

            $targets[] = Carbon::create($yearInt, $monthInt, 1)->startOfMonth();
        }

        $unique = [];
        foreach ($targets as $target) {
            $unique[$target->format('Y-m')] = $target;
        }
        ksort($unique);

        return array_values($unique);
    }

    private function followupHints(): array
    {
        return [
            'tuong tu',
            'giong',
            'nhu hom truoc',
            'nhu lan truoc',
            'xuat lai',
            'y nhu cu',
            'nhu cu',
            'y chang',
        ];
    }

    private function isFollowupRequest(string $normalized): bool
    {
        foreach ($this->followupHints() as $hint) {
            if (str_contains($normalized, $hint)) {
                return true;
            }
        }

        return false;
    }

    private function containsRevenueHint(string $normalized): bool
    {
        $hints = [
            'doanh thu',
            'revenue',
            'anh thu',
            'tong thu',
            'thu ve',
            'them doanh thu',
            'bo sung doanh thu',
        ];

        foreach ($hints as $hint) {
            if (str_contains($normalized, $hint)) {
                return true;
            }
        }

        return false;
    }

    private function extractExportFormat(string $normalized): string
    {
        if (str_contains($normalized, 'html') || str_contains($normalized, 'bieu do') || str_contains($normalized, 'chart')) {
            return 'html';
        }

        if (str_contains($normalized, 'pdf')) {
            return 'pdf';
        }

        if (str_contains($normalized, 'xlsx') || str_contains($normalized, 'excel')) {
            return 'xlsx';
        }

        return 'csv';
    }

    private function rebuildReportFromTemplate(User $user, array $template): ?array
    {
        $intent = (string) ($template['intent'] ?? 'unknown');

        return match ($intent) {
            'running_events' => $this->buildRunningEventsQuickReport($user),
            'clients_by_event' => $this->buildClientsByEventQuickReport(
                $user,
                '',
                '',
                (string) ($template['event_keyword'] ?? '')
            ),
            'event_overview' => $this->buildEventOverviewReport(
                $user,
                '',
                '',
                (string) ($template['event_keyword'] ?? '')
            ),
            'monthly_statistics' => $this->buildMonthlyStatistics(
                $user,
                $this->resolveMonthsFromTemplate($template),
                (bool) ($template['include_revenue'] ?? false)
            ),
            'yearly_report' => $this->buildYearlyReportWithCharts(
                $user,
                (int) ($template['year'] ?? now()->year),
                (bool) ($template['include_revenue'] ?? false)
            ),
            'top_events' => $this->buildTopEventsReport(
                $user,
                (int) ($template['year'] ?? now()->year)
            ),
            default => null,
        };
    }

    private function buildExportRows(string $intent, array $data): array
    {
        if ($intent === 'running_events') {
            $rows = [];
            foreach ((array) ($data['event_samples'] ?? []) as $item) {
                $rows[] = [
                    'event_id' => (int) ($item['id'] ?? 0),
                    'event_code' => (string) ($item['code'] ?? ''),
                    'event_name' => (string) ($item['name'] ?? ''),
                    'from_date' => (string) ($item['from_date'] ?? ''),
                    'to_date' => (string) ($item['to_date'] ?? ''),
                    'running_events_count' => (int) ($data['running_events_count'] ?? 0),
                ];
            }
            return $rows;
        }

        if (in_array($intent, ['clients_by_event', 'event_overview'], true)) {
            $event = (array) ($data['event'] ?? []);
            $rows = [[
                'event_id' => (int) ($event['id'] ?? 0),
                'event_code' => (string) ($event['code'] ?? ''),
                'event_name' => (string) ($event['name'] ?? ''),
                'total_clients' => (int) ($data['total_clients'] ?? 0),
                'checked_in_clients' => (int) ($data['checked_in_clients'] ?? 0),
                'checkin_rate_percent' => (float) ($data['checkin_rate_percent'] ?? 0),
            ]];

            foreach ((array) ($data['daily_checkins'] ?? []) as $daily) {
                $rows[] = [
                    'row_type' => 'daily_checkin',
                    'date_label' => (string) ($daily['date_label'] ?? ''),
                    'checkins' => (int) ($daily['checkins'] ?? 0),
                ];
            }
            foreach ((array) ($data['sources'] ?? []) as $source) {
                $rows[] = [
                    'row_type' => 'source_distribution',
                    'source' => (string) ($source['source'] ?? ''),
                    'source_label' => (string) ($source['source_label'] ?? ''),
                    'total' => (int) ($source['total'] ?? 0),
                ];
            }

            return $rows;
        }

        if ($intent === 'monthly_statistics') {
            return array_map(function ($row) {
                return [
                    'month' => (int) ($row['month'] ?? 0),
                    'year' => (int) ($row['year'] ?? 0),
                    'label' => (string) ($row['label'] ?? ''),
                    'total_events' => (int) ($row['total_events'] ?? 0),
                    'active_events' => (int) ($row['active_events'] ?? 0),
                    'done_events' => (int) ($row['done_events'] ?? 0),
                    'total_clients' => (int) ($row['total_clients'] ?? 0),
                    'checkedin_clients' => (int) ($row['checkedin_clients'] ?? 0),
                    'revenue' => (float) ($row['revenue'] ?? 0),
                ];
            }, (array) ($data['months'] ?? []));
        }

        if ($intent === 'yearly_report') {
            $rows = [];
            $eventsByMonth = (array) ($data['events_by_month'] ?? []);
            $clientsByMonth = (array) ($data['clients_by_month'] ?? []);
            $checkinsByMonth = (array) ($data['checkins_by_month'] ?? []);
            $revenueByMonth = (array) ($data['revenue_by_month'] ?? []);
            foreach (range(1, 12) as $month) {
                $index = $month - 1;
                $rows[] = [
                    'row_type' => 'monthly_summary',
                    'year' => (int) ($data['year'] ?? now()->year),
                    'month' => $month,
                    'events' => (int) ($eventsByMonth[$index] ?? 0),
                    'clients' => (int) ($clientsByMonth[$index] ?? 0),
                    'checkins' => (int) ($checkinsByMonth[$index] ?? 0),
                    'revenue' => (float) ($revenueByMonth[$index] ?? 0),
                ];
            }
            foreach ((array) ($data['top_events'] ?? []) as $event) {
                $rows[] = [
                    'row_type' => 'top_event',
                    'event_id' => (int) ($event['id'] ?? 0),
                    'event_code' => (string) ($event['code'] ?? ''),
                    'event_name' => (string) ($event['name'] ?? ''),
                    'total_clients' => (int) ($event['total_clients'] ?? 0),
                ];
            }
            return $rows;
        }

        if ($intent === 'top_events') {
            return array_map(function ($row) {
                return [
                    'event_id' => (int) ($row['event_id'] ?? 0),
                    'event_code' => (string) ($row['event_code'] ?? ''),
                    'event_name' => (string) ($row['event_name'] ?? ''),
                    'total_clients' => (int) ($row['total_clients'] ?? 0),
                    'total_checkins' => (int) ($row['total_checkins'] ?? 0),
                    'checkin_rate_percent' => (float) ($row['checkin_rate_percent'] ?? 0),
                ];
            }, (array) ($data['rows'] ?? []));
        }

        return [];
    }

    private function writeCsvExport(User $user, array $rows, string $intent): array
    {
        $firstRow = $rows[0] ?? [];
        $headers = array_keys((array) $firstRow);

        $lines = [];
        $lines[] = implode(',', array_map(fn ($header) => $this->escapeCsvValue($header), $headers));
        foreach ($rows as $row) {
            $lineValues = [];
            foreach ($headers as $header) {
                $lineValues[] = $this->escapeCsvValue((string) ($row[$header] ?? ''));
            }
            $lines[] = implode(',', $lineValues);
        }

        $timestamp = now()->format('Ymd_His');
        $fileName = "chatbot_report_{$intent}_{$timestamp}.csv";
        $path = 'chatbot-exports/' . (int) $user->id . '/' . $fileName;
        Storage::disk('public')->put($path, implode("\n", $lines));

        return [
            'file_name' => $fileName,
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
        ];
    }

    private function writeHtmlExport(User $user, string $intent, array $reportSnapshot, array $rows): array
    {
        $timestamp = now()->format('Ymd_His');
        $fileName = "chatbot_report_{$intent}_{$timestamp}.html";
        $path = 'chatbot-exports/' . (int) $user->id . '/' . $fileName;

        $title = 'Chatbot Report - ' . Str::upper(str_replace('_', ' ', $intent));
        $markdown = (string) ($reportSnapshot['markdown'] ?? '');
        $markdownHtml = nl2br(e($markdown));
        $tableHtml = $this->buildSimpleHtmlTable($rows);
        $charts = json_encode(array_values((array) ($reportSnapshot['charts'] ?? [])), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $html = <<<HTML
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>{$title}</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; color: #1f2937; }
    h1 { margin: 0 0 8px; font-size: 24px; }
    .muted { color: #6b7280; margin-bottom: 20px; }
    .card { border: 1px solid #e5e7eb; border-radius: 10px; padding: 14px; margin-bottom: 16px; }
    .chart { margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    th, td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; }
    th { background: #f9fafb; }
  </style>
</head>
<body>
  <h1>{$title}</h1>
  <div class="muted">Generated at: {$timestamp}</div>
  <div class="card">{$markdownHtml}</div>
  <div id="chart-root"></div>
  <div class="card">
    <h2>Export Data</h2>
    {$tableHtml}
  </div>

  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <script>
    const charts = {$charts} || [];
    const root = document.getElementById('chart-root');
    charts.forEach((chart, idx) => {
      const wrap = document.createElement('div');
      wrap.className = 'card chart';
      const title = document.createElement('h3');
      title.textContent = chart.title || ('Chart ' + (idx + 1));
      wrap.appendChild(title);
      const target = document.createElement('div');
      wrap.appendChild(target);
      root.appendChild(wrap);

      const type = chart.type || 'bar';
      const options = type === 'donut'
        ? {
            chart: { type: 'donut', height: Number(chart.height || 300), toolbar: { show: false } },
            labels: Array.isArray(chart.categories) ? chart.categories : [],
            series: Array.isArray(chart.series) ? chart.series : [],
            legend: { position: 'bottom' },
          }
        : {
            chart: { type, height: Number(chart.height || 300), toolbar: { show: false } },
            xaxis: { categories: Array.isArray(chart.categories) ? chart.categories : [] },
            series: Array.isArray(chart.series) ? chart.series : [],
            dataLabels: { enabled: false },
            stroke: { curve: type === 'line' ? 'smooth' : 'straight', width: 2 },
            legend: { position: 'bottom' },
          };
      const instance = new ApexCharts(target, options);
      instance.render();
    });
  </script>
</body>
</html>
HTML;

        Storage::disk('public')->put($path, $html);

        return [
            'file_name' => $fileName,
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
        ];
    }

    private function buildSimpleHtmlTable(array $rows): string
    {
        if (!count($rows)) {
            return '<p>No rows</p>';
        }

        $headers = array_keys((array) ($rows[0] ?? []));
        $thead = '<tr>' . implode('', array_map(fn ($header) => '<th>' . e((string) $header) . '</th>', $headers)) . '</tr>';

        $tbodyRows = array_map(function ($row) use ($headers) {
            $cells = [];
            foreach ($headers as $header) {
                $cells[] = '<td>' . e((string) ($row[$header] ?? '')) . '</td>';
            }
            return '<tr>' . implode('', $cells) . '</tr>';
        }, $rows);

        return '<table><thead>' . $thead . '</thead><tbody>' . implode('', $tbodyRows) . '</tbody></table>';
    }

    private function escapeCsvValue(string $value): string
    {
        $escaped = str_replace('"', '""', $value);
        return '"' . $escaped . '"';
    }

    private function resolveRevenueByMonth($eventIds, ?int $year = null): array
    {
        if (! $this->canUseRevenueTables()) {
            return [];
        }

        $eventIds = collect($eventIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values();

        if ($eventIds->isEmpty()) {
            return [];
        }

        $query = DB::table('orders')
            ->join('clients', 'clients.id', '=', 'orders.client_id')
            ->whereIn('clients.event_id', $eventIds->all())
            ->where('clients.status', '!=', Client::STATUS_DELETED)
            ->whereRaw('COALESCE(orders.price, 0) > 0')
            ->selectRaw('EXTRACT(MONTH FROM COALESCE(orders.created_at, clients.created_at)) as month_num, SUM(COALESCE(orders.price, 0)) as total')
            ->groupBy('month_num');

        if ($year && $year > 0) {
            $query->whereRaw('EXTRACT(YEAR FROM COALESCE(orders.created_at, clients.created_at)) = ?', [$year]);
        }

        return $query->pluck('total', 'month_num')
            ->map(fn ($value) => (float) $value)
            ->toArray();
    }

    private function canUseRevenueTables(): bool
    {
        return Schema::hasTable('orders')
            && Schema::hasTable('clients')
            && Schema::hasColumn('orders', 'client_id')
            && Schema::hasColumn('orders', 'price')
            && Schema::hasColumn('clients', 'event_id');
    }

    private function formatMoney(float $amount): string
    {
        $precision = abs($amount - round($amount)) < 0.00001 ? 0 : 2;

        return number_format($amount, $precision, ',', '.') . ' VND';
    }

    private function extractEventKeyword(string $originalMessage, string $normalized): string
    {
        $keyword = '';
        if (preg_match('/su kien\s+(.+)$/', $normalized, $match)) {
            $keyword = trim($match[1]);
        }

        if ($keyword !== '') {
            $keyword = preg_replace('/\b(co bao nhieu|bao nhieu|khach hang|thuoc|trong|hien tai|dang chay|dang dien ra|thong ke|bao cao|nam\s+20\d{2}|thang.+)\b.*/', '', $keyword) ?? $keyword;
            $keyword = trim($keyword, " .,!?:;");
        }

        if ($keyword !== '') {
            return $keyword;
        }

        $originalNormalized = $this->normalizeText($originalMessage);
        if (preg_match('/su kien\s+(.+)$/', $originalNormalized, $match)) {
            return trim($match[1], " .,!?:;");
        }

        return '';
    }

    private function findEventByKeyword(User $user, string $keyword): array
    {
        $query = $this->eventScopeForUser($user);
        $normalizedKeyword = $this->normalizeText($keyword);
        $compactKeyword = $this->normalizeCompactToken($keyword);

        $likeValue = '%' . $keyword . '%';
        $normalizedLikeValue = '%' . $normalizedKeyword . '%';
        $compactLikeValue = '%' . $compactKeyword . '%';

        $matches = (clone $query)
            ->where(function (Builder $query) use ($likeValue, $normalizedKeyword, $normalizedLikeValue, $compactKeyword, $compactLikeValue) {
                $query->where('name', 'like', $likeValue)
                    ->orWhere('code', 'like', $likeValue);

                if ($normalizedKeyword !== '') {
                    $query->orWhereRaw('LOWER(name) LIKE ?', [$normalizedLikeValue])
                        ->orWhereRaw('LOWER(code) LIKE ?', [$normalizedLikeValue]);
                }

                if ($compactKeyword !== '') {
                    $query->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(LOWER(name), ' ', ''), '-', ''), '_', ''), '/', '') LIKE ?", [$compactLikeValue])
                        ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(LOWER(code), ' ', ''), '-', ''), '_', ''), '/', '') LIKE ?", [$compactLikeValue]);
                }
            })
            ->orderBy('from_date', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'code', 'from_date', 'to_date']);

        if ($matches->isNotEmpty()) {
            $event = $matches->first();
            if ($matches->count() > 1) {
                $event = $matches->sortByDesc(function ($item) use ($normalizedKeyword) {
                    similar_text($normalizedKeyword, $this->normalizeText($item->name), $nameScore);
                    similar_text($normalizedKeyword, $this->normalizeText($item->code), $codeScore);
                    return max($nameScore, $codeScore);
                })->first();
            }

            return [$event, $matches->take(5)->values()->all()];
        }

        if ($compactKeyword !== '') {
            $fuzzyCandidates = (clone $query)
                ->orderBy('from_date', 'desc')
                ->limit(200)
                ->get(['id', 'name', 'code', 'from_date', 'to_date'])
                ->map(function ($item) use ($compactKeyword) {
                    similar_text($compactKeyword, $this->normalizeCompactToken((string) $item->name), $nameScore);
                    similar_text($compactKeyword, $this->normalizeCompactToken((string) $item->code), $codeScore);
                    $item->fuzzy_score = max($nameScore, $codeScore);

                    return $item;
                })
                ->sortByDesc('fuzzy_score')
                ->values();

            $bestMatch = $fuzzyCandidates->first();
            if ($bestMatch && (float) ($bestMatch->fuzzy_score ?? 0) >= 45) {
                return [$bestMatch, $fuzzyCandidates->take(5)->all()];
            }
        }

        $fallbackCandidates = (clone $query)
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'code']);

        return [null, $fallbackCandidates->values()->all()];
    }

    private function eventScopeForUser(User $user): Builder
    {
        $query = Event::query()
            ->where('status', '!=', BaseModel::STATUS_DELETED)
            ->where('status', '!=', Event::STATUS_HIDDEN);

        if ($this->isSystemAdmin($user)) {
            return $query;
        }

        if ($this->isCompanyAdmin($user)) {
            $companyId = (int) $user->company_id;
            if ($companyId <= 0) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where('company_id', $companyId);
        }

        throw new AuthorizationException('Bạn không có quyền truy xuất báo cáo dữ liệu.');
    }

    private function isSystemAdmin(User $user): bool
    {
        return (bool) $user->is_admin && $user->hasRole(Role::ROLE_ADMIN);
    }

    private function isCompanyAdmin(User $user): bool
    {
        return ! (bool) $user->is_admin && $user->hasRole(Role::ROLE_ADMIN);
    }

    private function normalizeText(string $text): string
    {
        $text = Str::ascii(Str::lower($text));
        $text = preg_replace('/[^a-z0-9\s\/\-_]/', ' ', $text) ?? '';
        $text = preg_replace('/\s+/', ' ', $text) ?? '';

        return trim($text);
    }

    private function normalizeCompactToken(string $text): string
    {
        $normalized = $this->normalizeText($text);

        return preg_replace('/[\s\/\-_]+/', '', $normalized) ?? '';
    }
}
