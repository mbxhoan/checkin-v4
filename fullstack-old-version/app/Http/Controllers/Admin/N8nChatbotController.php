<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\N8nChatMessage;
use App\Models\N8nChatSession;
use App\Models\Role;
use App\Models\User;
use App\Services\Admin\N8nChatbotAgentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class N8nChatbotController extends Controller
{
    public function __construct(private readonly N8nChatbotAgentService $agentService) {}

    public function history(Request $request): JsonResponse
    {
        if (($disabledResponse = $this->chatbotDisabledResponse()) !== null) {
            return $disabledResponse;
        }

        $user = $request->user();
        $this->agentService->ensureReportPermission($user);

        $selectedSessionId = $request->filled('session_id')
            ? (int) $request->input('session_id')
            : null;

        return response()->json(
            $this->buildHistoryPayload($user, $selectedSessionId)
        );
    }

    public function selectMode(Request $request): JsonResponse
    {
        if (($disabledResponse = $this->chatbotDisabledResponse()) !== null) {
            return $disabledResponse;
        }

        $user = $request->user();
        $this->agentService->ensureReportPermission($user);

        $validated = $request->validate([
            'mode' => ['required', 'in:'.implode(',', [
                N8nChatSession::MODE_GUIDE,
                N8nChatSession::MODE_REPORT,
                N8nChatSession::MODE_SUPPORT,
            ])],
        ]);

        $session = $this->getOrCreateActiveSession($user);
        $mode = (string) $validated['mode'];

        if ($this->normalizeMode($session->mode) !== $mode) {
            $session->update([
                'mode' => $mode,
            ]);
        }

        $assistantMessage = $this->createAssistantMessage(
            $session,
            $this->agentService->getModeAckMarkdown($mode),
            [
                'kind' => 'mode_ack',
                'mode' => $mode,
            ]
        );

        return response()->json([
            'active_session_id' => $session->id,
            'session_mode' => $mode,
            'assistant_message' => $this->transformMessage($assistantMessage),
            'sessions' => $this->sessionSummaries($user, $session->id, $session->id),
        ]);
    }

    public function send(Request $request): JsonResponse
    {
        if (($disabledResponse = $this->chatbotDisabledResponse()) !== null) {
            return $disabledResponse;
        }

        $user = $request->user();
        $this->agentService->ensureReportPermission($user);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
        ]);

        $session = $this->getOrCreateActiveSession($user);

        $userMessage = $session->messages()->create([
            'user_id' => $user->id,
            'role' => N8nChatMessage::ROLE_USER,
            'content' => $validated['message'],
        ]);

        $mode = $this->normalizeMode($session->mode);
        if ($mode === N8nChatSession::MODE_UNSET) {
            $detectedMode = $this->agentService->detectModeFromMessage($validated['message']);

            if ($detectedMode === null) {
                $assistantMessage = $this->createAssistantMessage(
                    $session,
                    $this->agentService->getModeSelectionMarkdown(),
                    [
                        'kind' => 'mode_picker',
                        'actions' => $this->agentService->buildModeSelectionActions(),
                    ]
                );

                return response()->json([
                    'active_session_id' => $session->id,
                    'session_mode' => $mode,
                    'user_message' => $this->transformMessage($userMessage),
                    'assistant_message' => $this->transformMessage($assistantMessage),
                ]);
            }

            $session->update([
                'mode' => $detectedMode,
            ]);
            $mode = $detectedMode;
        }

        if ($mode === N8nChatSession::MODE_REPORT) {
            $agentReply = $this->agentService->handleReportQuery($user, $validated['message']);

            $assistantMessage = $this->createAssistantMessage(
                $session,
                $agentReply['markdown'] ?? 'Khong co du lieu phan hoi.',
                [
                    'kind' => 'report',
                    'category' => $agentReply['category'] ?? N8nChatbotAgentService::CATEGORY_QUICK_REPORT,
                    'intent' => $agentReply['intent'] ?? 'unknown',
                    'charts' => $agentReply['charts'] ?? [],
                    'actions' => $agentReply['actions'] ?? [],
                    'data' => $agentReply['data'] ?? [],
                    'template' => $agentReply['template'] ?? null,
                    'memory' => $agentReply['memory'] ?? null,
                    'user_query' => $validated['message'],
                ]
            );

            return response()->json([
                'active_session_id' => $session->id,
                'session_mode' => $mode,
                'user_message' => $this->transformMessage($userMessage),
                'assistant_message' => $this->transformMessage($assistantMessage),
            ]);
        }

        if ($mode === N8nChatSession::MODE_SUPPORT) {
            $agentReply = $this->agentService->handleSupportQuery(
                $user,
                $validated['message'],
                (int) $session->id
            );

            $assistantMessage = $this->createAssistantMessage(
                $session,
                $agentReply['markdown'] ?? 'Không có dữ liệu phản hồi.',
                [
                    'kind' => 'support',
                    'category' => $agentReply['category'] ?? N8nChatbotAgentService::CATEGORY_ISSUE_SUPPORT,
                    'intent' => $agentReply['intent'] ?? 'issue_guide',
                    'charts' => $agentReply['charts'] ?? [],
                    'actions' => $agentReply['actions'] ?? [],
                    'data' => $agentReply['data'] ?? [],
                    'user_query' => $validated['message'],
                ]
            );

            return response()->json([
                'active_session_id' => $session->id,
                'session_mode' => $mode,
                'user_message' => $this->transformMessage($userMessage),
                'assistant_message' => $this->transformMessage($assistantMessage),
            ]);
        }

        if ($mode === N8nChatSession::MODE_GUIDE && $this->agentService->isIssueRelatedMessage($validated['message'])) {
            $session->update([
                'mode' => N8nChatSession::MODE_SUPPORT,
            ]);
            $mode = N8nChatSession::MODE_SUPPORT;

            $agentReply = $this->agentService->handleSupportQuery(
                $user,
                $validated['message'],
                (int) $session->id
            );

            $assistantMessage = $this->createAssistantMessage(
                $session,
                $agentReply['markdown'] ?? 'Không có dữ liệu phản hồi.',
                [
                    'kind' => 'support',
                    'category' => $agentReply['category'] ?? N8nChatbotAgentService::CATEGORY_ISSUE_SUPPORT,
                    'intent' => $agentReply['intent'] ?? 'issue_guide',
                    'charts' => $agentReply['charts'] ?? [],
                    'actions' => $agentReply['actions'] ?? [],
                    'data' => $agentReply['data'] ?? [],
                    'user_query' => $validated['message'],
                    'auto_switched_from' => N8nChatSession::MODE_GUIDE,
                ]
            );

            return response()->json([
                'active_session_id' => $session->id,
                'session_mode' => $mode,
                'user_message' => $this->transformMessage($userMessage),
                'assistant_message' => $this->transformMessage($assistantMessage),
            ]);
        }

        $assistantMessage = $this->handleGuideMessage($session, $validated['message']);

        return response()->json([
            'active_session_id' => $session->id,
            'session_mode' => $mode,
            'user_message' => $this->transformMessage($userMessage),
            'assistant_message' => $this->transformMessage($assistantMessage),
        ]);
    }

    public function reset(Request $request): JsonResponse
    {
        if (($disabledResponse = $this->chatbotDisabledResponse()) !== null) {
            return $disabledResponse;
        }

        $user = $request->user();
        $this->agentService->ensureReportPermission($user);

        $session = DB::transaction(function () use ($user) {
            N8nChatSession::query()
                ->where('user_id', $user->id)
                ->where('status', N8nChatSession::STATUS_ACTIVE)
                ->update([
                    'status' => N8nChatSession::STATUS_CLOSED,
                    'closed_at' => now(),
                    'updated_at' => now(),
                ]);

            return $this->createActiveSession($user);
        });

        return response()->json(
            $this->buildHistoryPayload($user, $session->id)
        );
    }

    private function handleGuideMessage(N8nChatSession $session, string $message): N8nChatMessage
    {
        $webhookUrl = trim((string) config('services.n8n_chatbot.webhook_url', ''));
        if ($webhookUrl === '') {
            return $this->createAssistantMessage(
                $session,
                'Missing N8N webhook URL. Set N8N_CHATBOT_WEBHOOK_URL in .env.',
                [
                    'kind' => 'error',
                ]
            );
        }

        $client = Http::acceptJson()
            ->asJson()
            ->timeout(30);

        $auth = trim((string) config('services.n8n_chatbot.auth', ''));
        if ($auth !== '') {
            $client = $client->withHeaders([
                'Authorization' => $auth,
            ]);
        }

        try {
            $user = $session->user()->first();
            $response = $client->post($webhookUrl, [
                'message' => $message,
                'session_id' => $session->id,
                'session_mode' => $this->normalizeMode($session->mode),
                'history' => $this->buildContextMessages($session),
                'user_context' => $user ? $this->buildWebhookUserContext($user) : null,
                'memory_context' => $user ? $this->agentService->buildUserMemoryContext($user) : null,
                'cross_session_history' => $user ? $this->buildCrossSessionContext($user) : [],
            ]);
        } catch (\Throwable $exception) {
            report($exception);
            Log::error('N8n webhook error', [
                'error' => $exception->getMessage(),
            ]);

            return $this->createAssistantMessage(
                $session,
                'Không thể kết nối. Vui lòng thử lại sau.',
                [
                    'kind' => 'error',
                ]
            );
        }

        if (! $response->successful()) {
            Log::error('N8n webhook error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return $this->createAssistantMessage(
                $session,
                'Kết nối không ổn định. Vui lòng thử lại sau.',
                [
                    'kind' => 'error',
                    'status' => $response->status(),
                    'details' => $response->json() ?? $response->body(),
                ]
            );
        }

        $payload = $response->json();
        $output = $this->extractOutput($payload, $response->body());
        $guideMeta = $this->extractGuideResponseMeta($payload);

        return $this->createAssistantMessage(
            $session,
            $output,
            array_merge([
                'kind' => 'guide',
                'source' => 'n8n_webhook',
            ], $guideMeta)
        );
    }

    private function buildHistoryPayload(User $user, ?int $selectedSessionId = null): array
    {
        $activeSession = $this->getOrCreateActiveSession($user);
        $selectedSession = $activeSession;

        if (! empty($selectedSessionId) && $selectedSessionId !== $activeSession->id) {
            $candidate = N8nChatSession::query()
                ->where('user_id', $user->id)
                ->where('id', $selectedSessionId)
                ->first();

            if ($candidate) {
                $selectedSession = $candidate;
            }
        }

        $messages = $selectedSession->messages()
            ->orderBy('id')
            ->get()
            ->map(fn (N8nChatMessage $message) => $this->transformMessage($message))
            ->values();

        return [
            'active_session_id' => $activeSession->id,
            'selected_session_id' => $selectedSession->id,
            'session_mode' => $this->normalizeMode($selectedSession->mode),
            'read_only' => $selectedSession->id !== $activeSession->id,
            'can_chat' => $selectedSession->id === $activeSession->id,
            'messages' => $messages,
            'sessions' => $this->sessionSummaries($user, $activeSession->id, $selectedSession->id),
        ];
    }

    private function sessionSummaries(User $user, int $activeSessionId, int $selectedSessionId): array
    {
        $sessions = N8nChatSession::query()
            ->where('user_id', $user->id)
            ->withCount('messages')
            ->addSelect([
                'last_message_content' => N8nChatMessage::query()
                    ->select('content')
                    ->whereColumn('session_id', 'n8n_chat_sessions.id')
                    ->latest('id')
                    ->limit(1),
                'last_message_at' => N8nChatMessage::query()
                    ->select('created_at')
                    ->whereColumn('session_id', 'n8n_chat_sessions.id')
                    ->latest('id')
                    ->limit(1),
            ])
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        return $sessions
            ->map(function (N8nChatSession $session) use ($activeSessionId, $selectedSessionId) {
                $isActive = $session->id === $activeSessionId;
                $createdAt = $session->created_at
                    ? Carbon::parse($session->created_at)->format('d/m H:i')
                    : 'N/A';
                $mode = $this->normalizeMode($session->mode);
                $modeLabel = match ($mode) {
                    N8nChatSession::MODE_REPORT => 'Report',
                    N8nChatSession::MODE_GUIDE => 'Guide',
                    N8nChatSession::MODE_SUPPORT => 'Support',
                    default => 'Unset',
                };

                return [
                    'id' => $session->id,
                    'mode' => $mode,
                    'mode_label' => $modeLabel,
                    'status' => $session->status,
                    'message_count' => (int) $session->messages_count,
                    'preview' => Str::limit((string) ($session->last_message_content ?? ''), 80),
                    'created_at' => optional($session->created_at)->toIso8601String(),
                    'closed_at' => optional($session->closed_at)->toIso8601String(),
                    'last_message_at' => ! empty($session->last_message_at)
                        ? Carbon::parse($session->last_message_at)->toIso8601String()
                        : null,
                    'is_active' => $isActive,
                    'is_selected' => $session->id === $selectedSessionId,
                    'can_chat' => $isActive,
                    'label' => ($isActive ? 'Hiện tại' : 'Lịch sử')." #{$session->id} - {$modeLabel} - {$createdAt}",
                ];
            })
            ->values()
            ->all();
    }

    private function getOrCreateActiveSession(User $user): N8nChatSession
    {
        $session = N8nChatSession::query()
            ->where('user_id', $user->id)
            ->where('status', N8nChatSession::STATUS_ACTIVE)
            ->latest('id')
            ->first();

        if ($session) {
            N8nChatSession::query()
                ->where('user_id', $user->id)
                ->where('status', N8nChatSession::STATUS_ACTIVE)
                ->where('id', '!=', $session->id)
                ->update([
                    'status' => N8nChatSession::STATUS_CLOSED,
                    'closed_at' => now(),
                    'updated_at' => now(),
                ]);

            if (empty($session->mode)) {
                $session->update([
                    'mode' => N8nChatSession::MODE_UNSET,
                ]);
            }

            $this->ensureSessionHasOpeningPrompt($session);

            return $session;
        }

        return $this->createActiveSession($user);
    }

    private function createActiveSession(User $user): N8nChatSession
    {
        $session = N8nChatSession::create([
            'user_id' => $user->id,
            'status' => N8nChatSession::STATUS_ACTIVE,
            'mode' => N8nChatSession::MODE_UNSET,
            'started_at' => now(),
        ]);

        $this->ensureSessionHasOpeningPrompt($session);

        return $session;
    }

    private function ensureSessionHasOpeningPrompt(N8nChatSession $session): void
    {
        if ($session->messages()->exists()) {
            return;
        }

        $this->createAssistantMessage(
            $session,
            $this->agentService->getModeSelectionMarkdown(),
            [
                'kind' => 'mode_picker',
                'actions' => $this->agentService->buildModeSelectionActions(),
            ]
        );
    }

    private function createAssistantMessage(N8nChatSession $session, string $content, array $meta = []): N8nChatMessage
    {
        return $session->messages()->create([
            'user_id' => $session->user_id,
            'role' => N8nChatMessage::ROLE_ASSISTANT,
            'content' => $content,
            'content_html' => $this->renderMarkdown($content),
            'meta' => $meta,
        ]);
    }

    private function normalizeMode(?string $mode): string
    {
        $mode = strtoupper((string) $mode);

        return in_array($mode, [
            N8nChatSession::MODE_GUIDE,
            N8nChatSession::MODE_REPORT,
            N8nChatSession::MODE_SUPPORT,
        ], true) ? $mode : N8nChatSession::MODE_UNSET;
    }

    private function transformMessage(N8nChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'role' => $message->role,
            'content' => $message->content,
            'content_html' => $message->content_html,
            'meta' => $message->meta,
            'created_at' => optional($message->created_at)->toIso8601String(),
        ];
    }

    private function extractOutput(mixed $payload, string $rawBody): string
    {
        $output = $this->findFirstOutput($payload);
        if ($output !== null) {
            return $output;
        }

        if (is_string($payload) && trim($payload) !== '') {
            return $payload;
        }

        return trim($rawBody);
    }

    private function extractGuideResponseMeta(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $meta = [];
        $rawMeta = data_get($payload, 'meta');
        if (is_array($rawMeta)) {
            $meta = array_merge($meta, $rawMeta);
        }

        $charts = data_get($payload, 'charts');
        if (is_array($charts)) {
            $meta['charts'] = $charts;
        }

        $actions = data_get($payload, 'actions');
        if (is_array($actions)) {
            $meta['actions'] = $actions;
        }

        if (isset($payload['intent']) && ! isset($meta['intent'])) {
            $meta['intent'] = (string) $payload['intent'];
        }

        if (! isset($meta['category']) && isset($payload['category'])) {
            $meta['category'] = (string) $payload['category'];
        }

        return $meta;
    }

    private function findFirstOutput(mixed $payload): ?string
    {
        if (! is_array($payload)) {
            return null;
        }

        $directOutput = $payload['output'] ?? null;
        if (is_string($directOutput) && trim($directOutput) !== '') {
            return $directOutput;
        }

        foreach ($payload as $item) {
            $nestedOutput = $this->findFirstOutput($item);
            if ($nestedOutput !== null) {
                return $nestedOutput;
            }
        }

        return null;
    }

    private function buildContextMessages(N8nChatSession $session): array
    {
        return $session->messages()
            ->whereIn('role', [
                N8nChatMessage::ROLE_USER,
                N8nChatMessage::ROLE_ASSISTANT,
            ])
            ->orderByDesc('id')
            ->limit(20)
            ->get(['role', 'content', 'meta'])
            ->reverse()
            ->values()
            ->filter(function (N8nChatMessage $message) {
                $kind = data_get($message->meta, 'kind');

                return $kind !== 'mode_picker';
            })
            ->map(fn (N8nChatMessage $message) => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->values()
            ->all();
    }

    private function buildWebhookUserContext(User $user): array
    {
        return [
            'id' => (int) $user->id,
            'name' => (string) $user->name,
            'company_id' => (int) ($user->company_id ?? 0),
            'event_id' => (int) ($user->event_id ?? 0),
            'is_admin' => (bool) $user->is_admin,
            'roles' => $user->roles->pluck('name')->values()->all(),
            'role_scope' => [
                'is_system_admin' => (bool) $user->is_admin && $user->hasRole(Role::ROLE_ADMIN),
                'is_company_admin' => ! (bool) $user->is_admin && $user->hasRole(Role::ROLE_ADMIN),
            ],
        ];
    }

    private function buildCrossSessionContext(User $user, int $limit = 40): array
    {
        return N8nChatMessage::query()
            ->whereIn('role', [
                N8nChatMessage::ROLE_USER,
                N8nChatMessage::ROLE_ASSISTANT,
            ])
            ->whereHas('session', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderByDesc('id')
            ->limit(max(10, min($limit, 120)))
            ->get(['role', 'content', 'meta', 'created_at'])
            ->reverse()
            ->values()
            ->filter(function (N8nChatMessage $message) {
                return data_get($message->meta, 'kind') !== 'mode_picker';
            })
            ->map(function (N8nChatMessage $message) {
                return [
                    'role' => $message->role,
                    'content' => $message->content,
                    'kind' => data_get($message->meta, 'kind'),
                    'created_at' => optional($message->created_at)->toIso8601String(),
                ];
            })
            ->values()
            ->all();
    }

    private function renderMarkdown(string $markdown): string
    {
        if (trim($markdown) === '') {
            return '';
        }

        return (string) Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'renderer' => [
                'soft_break' => "<br />\n",
            ],
        ]);
    }

    private function chatbotDisabledResponse(): ?JsonResponse
    {
        if ($this->isChatbotEnabled()) {
            return null;
        }

        return response()->json([
            'message' => 'Chatbot đang tạm tắt trong giai đoạn beta.',
            'chatbot_enabled' => false,
        ], 503);
    }

    private function isChatbotEnabled(): bool
    {
        return (bool) config('services.n8n_chatbot.enabled', true);
    }
}
