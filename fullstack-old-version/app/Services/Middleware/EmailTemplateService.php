<?php

namespace App\Services\Middleware;

use App\Helpers\Helper;
use App\HttpClient\HttpClient;
use App\Models\EmailTemplate;
use App\Models\Role;
use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailTemplateService extends BaseService
{
    private ?array $paidTemplateCatalogCache = null;

    private ?array $paidTemplateOverridesCache = null;

    public function __construct()
    {
        $this->model = resolve(EmailTemplate::class);
        $this->httpClient = new HttpClient(env('POSTMARK_API_URL'), [
            'Accept' => 'application/json',
            'X-Postmark-Server-Token' => env('POSTMARK_TOKEN'),
        ]);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function company()
    {
        return app(CompanyService::class);
    }

    public function email()
    {
        return app(EmailService::class);
    }

    public function middleware_client()
    {
        // return app(MiddlewareClientService::class);
    }

    public function getAuthorizedPostmarkTemplateIds()
    {
        $postmarkTemplateIds = [];

        if (Auth::check() && ! auth()->user()->isSysAdmin()) {
            $postmarkTemplateIds = auth()->user()->company->templates;
            if ($postmarkTemplateIds) {
                $postmarkTemplateIds = json_decode($postmarkTemplateIds, true);
            }
        }

        $normalized = [];
        foreach ((array) $postmarkTemplateIds as $templateId) {
            $id = (int) $templateId;
            if ($id <= 0) {
                continue;
            }
            $normalized[$id] = $id;
        }

        return array_values($normalized);
    }

    public function canUseTemplate(int $templateId): bool
    {
        if ($templateId <= 0) {
            return false;
        }

        if (! Auth::check()) {
            return true;
        }

        if (auth()->user()->isSysAdmin()) {
            return true;
        }

        return in_array($templateId, $this->getAuthorizedPostmarkTemplateIds(), true);
    }

    public function isPaidTemplate(int $templateId): bool
    {
        return $this->getPaidTemplateMeta($templateId) !== null;
    }

    public function getPaidTemplateMeta(int $templateId): ?array
    {
        $templateId = (int) $templateId;
        if ($templateId <= 0) {
            return null;
        }

        return $this->getPaidTemplateCatalog()[$templateId] ?? null;
    }

    public function getUnlockContactUrl(int $templateId, ?string $templateName = null): ?string
    {
        $contactUrl = trim((string) config('postmark_paid_templates.contact_url', ''));
        if ($contactUrl === '') {
            return null;
        }

        $templateName = trim((string) $templateName);
        $user = Auth::user();
        $companyName = trim((string) optional(optional($user)->company)->name);

        $replacements = [
            '{template_id}' => (string) $templateId,
            '{template_name}' => rawurlencode($templateName),
            '{user_email}' => rawurlencode((string) (optional($user)->email ?? '')),
            '{user_name}' => rawurlencode((string) (optional($user)->name ?? '')),
            '{company_name}' => rawurlencode($companyName),
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $contactUrl
        );
    }

    public function savePaidTemplateAccess(int $templateId, array $attributes): bool
    {
        $templateId = (int) $templateId;
        if ($templateId <= 0) {
            return false;
        }

        $emailTemplate = EmailTemplate::query()->firstOrNew([
            'uuid' => (string) $templateId,
        ]);

        if (! $emailTemplate->exists) {
            $emailTemplate->name = trim((string) ($attributes['name'] ?? '')) ?: "Template #{$templateId}";
            $emailTemplate->status = 'ACTIVE';
        } elseif (! empty($attributes['name'])) {
            $emailTemplate->name = trim((string) $attributes['name']);
        }

        if (! empty($attributes['subject']) && empty($emailTemplate->subject)) {
            $emailTemplate->subject = (string) $attributes['subject'];
        }

        $texts = is_array($emailTemplate->texts) ? $emailTemplate->texts : [];
        $texts['paid_access'] = $this->normalizePaidTemplateMeta($attributes, $templateId);
        $emailTemplate->texts = $texts;
        $emailTemplate->save();

        $this->paidTemplateCatalogCache = null;
        $this->paidTemplateOverridesCache = null;

        return true;
    }

    public function requestUnlockPaidTemplate(int $templateId): array
    {
        $templateId = (int) $templateId;
        $user = Auth::user();
        if (! $user) {
            return [
                'ok' => false,
                'message' => 'Bạn cần đăng nhập để đăng ký mở khoá.',
            ];
        }

        $paidTemplateMeta = $this->getPaidTemplateMeta($templateId);
        if (! $paidTemplateMeta) {
            return [
                'ok' => false,
                'message' => 'Template này không thuộc nhóm trả phí.',
            ];
        }

        if ($this->canUseTemplate($templateId)) {
            return [
                'ok' => false,
                'message' => 'Template này đã được mở khoá cho tài khoản/công ty của bạn.',
            ];
        }

        $postmarkToken = (string) config('services.postmark.token');
        $fromAddress = (string) config('mail.from.address');
        $fromName = (string) config('mail.from.name', config('app.name'));
        $notifyTo = trim((string) config('postmark_paid_templates.notify_to', 'admin@delfi.vn'));

        if ($postmarkToken === '' || $fromAddress === '' || $notifyTo === '') {
            return [
                'ok' => false,
                'message' => 'Thiếu cấu hình gửi email thông báo mở khoá template.',
            ];
        }

        $from = $fromName !== '' ? "{$fromName} <{$fromAddress}>" : $fromAddress;
        $apiUrl = rtrim((string) env('POSTMARK_API_URL', 'https://api.postmarkapp.com'), '/');
        $companyName = trim((string) optional(optional($user)->company)->name);

        $templateName = (string) ($paidTemplateMeta['event_name'] ?? '');
        $templateTime = (string) ($paidTemplateMeta['event_time'] ?? '');
        $credit = (string) ($paidTemplateMeta['credit'] ?? '');

        $subject = "[UNLOCK TEMPLATE] Yêu cầu mở khoá template #{$templateId}";
        $textBody = implode("\n", [
            'Có yêu cầu mở khoá template trả phí mới.',
            "Template ID: {$templateId}",
            "Tên template/sự kiện: {$templateName}",
            "Thời gian sự kiện: {$templateTime}",
            "Credit: {$credit}",
            "Người yêu cầu: {$user->name} ({$user->email})",
            'Company ID: '.(int) ($user->company_id ?? 0),
            "Tên công ty: {$companyName}",
            'User ID: '.(int) $user->id,
            'Thời gian yêu cầu: '.now()->format('Y-m-d H:i:s'),
        ]);

        $payload = [
            'From' => $from,
            'To' => $notifyTo,
            'Subject' => $subject,
            'TextBody' => $textBody,
            'HtmlBody' => nl2br(e($textBody)),
            'Tag' => "PAID-TEMPLATE-UNLOCK-{$templateId}",
            'MessageStream' => env('POSTMARK_MESSAGE_STREAM', 'outbound'),
        ];

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Postmark-Server-Token' => $postmarkToken,
            ])->post("{$apiUrl}/email", $payload);
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'message' => $exception->getMessage(),
            ];
        }

        if (! $response->successful()) {
            return [
                'ok' => false,
                'message' => "HTTP {$response->status()} - {$response->body()}",
            ];
        }

        $body = $response->json();
        if (is_array($body) && isset($body['ErrorCode']) && (int) $body['ErrorCode'] !== 0) {
            return [
                'ok' => false,
                'message' => "Postmark {$body['ErrorCode']}: ".($body['Message'] ?? 'Lỗi không xác định'),
            ];
        }

        return [
            'ok' => true,
            'notify_email' => (string) $user->email,
        ];
    }

    public function getAuthorizedPostmarkTemplates(array $templates = [], bool $includeLockedPaidPreview = false)
    {
        if (! count($templates)) {
            return [];
        }

        $authorizedLookup = [];
        foreach ($this->getAuthorizedPostmarkTemplateIds() as $authorizedTemplateId) {
            $authorizedLookup[(int) $authorizedTemplateId] = true;
        }

        $isAuthenticated = Auth::check();
        $isSysAdmin = $isAuthenticated && auth()->user()->isSysAdmin();
        $filteredTemplates = [];

        foreach ($templates as $template) {
            $templateId = (int) ($template['TemplateId'] ?? 0);
            if ($templateId <= 0) {
                continue;
            }

            $canUseTemplate = ! $isAuthenticated || $isSysAdmin || isset($authorizedLookup[$templateId]);
            $isPaidTemplate = $this->isPaidTemplate($templateId);
            $isLockedPaidTemplate = $isPaidTemplate && ! $canUseTemplate;

            if ($isAuthenticated && ! $isSysAdmin && ! $canUseTemplate) {
                if (! $includeLockedPaidPreview || ! $isLockedPaidTemplate) {
                    continue;
                }
            }

            $filteredTemplates[] = $this->appendTemplateAccessMeta($template, $canUseTemplate);
        }

        return array_values($filteredTemplates);
    }

    public function getPostmarkTemplates(
        bool $resync = false,
        bool $withDetails = false,
        bool $includeLockedPaidPreview = false
    ) {
        try {
            $params = [
                'count' => 100,
                'offset' => 0,
                'LayoutTemplate' => null,
                'TemplateType' => 'Standard', // Layout là layouts, Standard là templates
            ];

            $result = $this->httpClient->get('templates', $params);

            if ($result && (isset($result['TotalCount']) && isset($result['Templates']))) {
                if ($withDetails) {
                    // Heavy sync: fetch each template detail (HtmlBody/FullHtmlBody) and cache it.
                    foreach ($result['Templates'] as $index => $template) {
                        $postMarkTemplate = $this->getPostmarkTemplate(
                            (int) ($template['TemplateId'] ?? 0),
                            $resync,
                            $includeLockedPaidPreview
                        );
                        $result['Templates'][$index] = $postMarkTemplate;
                    }
                }

                /* authorize templates */
                $templates = $this->getAuthorizedPostmarkTemplates(
                    $result['Templates'],
                    $includeLockedPaidPreview
                );
                $result['Templates'] = $templates;
                $result['TotalCount'] = count($templates);

                return $result;
            }

            Log::info($result);
        } catch (\Exception $e) {
            Log::error("Call API Postmark - Get Templates: {$e->getMessage()}");
        }

        return [];
    }

    public function getPostmarkTemplate(
        int $templateId,
        bool $updateToRedis = false,
        bool $allowLockedPreview = false
    ) {
        $templateId = (int) $templateId;
        $canUseTemplate = $this->canUseTemplate($templateId);
        $canPreviewLockedContent = $allowLockedPreview
            && ! $canUseTemplate
            && $this->isPaidTemplate($templateId)
            && $this->canPreviewLockedTemplateContent();

        if (! $canUseTemplate) {
            if (! $allowLockedPreview || ! $this->isPaidTemplate($templateId)) {
                return [];
            }

            if (! $canPreviewLockedContent) {
                return $this->appendTemplateAccessMeta([
                    'TemplateId' => $templateId,
                    'Name' => "Template #{$templateId}",
                    'Subject' => null,
                    'HtmlBody' => null,
                ], false);
            }
        }

        $template = $this->getRedis('postmark', "email_template-{$templateId}", 'array');

        if (! $updateToRedis) {
            if (count($template) && isset($template['HtmlBody'])) {
                return $this->appendTemplateAccessMeta($template, $canUseTemplate);
            }
        }

        try {
            $result = $this->httpClient->get("templates/{$templateId}");

            if (isset($result['LayoutTemplate'])) {
                $layout = $result['LayoutTemplate'];
                $postmarkLayout = $this->httpClient->get("templates/{$layout}");

                // Replace {{{ @content }}} with child content
                $result['FullHtmlBody'] = str_replace('{{{ @content }}}', $result['HtmlBody'], $postmarkLayout['HtmlBody']);
            }

            if ($result && (isset($result['HtmlBody']))) {
                $result['placeholders'] = Helper::getPlaceholdersForPostmark([
                    $result['HtmlBody'],
                    $result['Subject'],
                ]);
                $this->updateRedis('postmark', "email_template-{$templateId}", json_encode($result), config('app.times.minutes.30'));

                /* upsert to DB */
                $this->upsert($templateId, [
                    'uuid' => $templateId,
                    'name' => $result['Name'],
                    'subject' => $result['Subject'],
                    'html' => $result['FullHtmlBody'] ?? ($result['HtmlBody'] ?? null),
                    'status' => 'NEW',
                ]);
                /* end */

                return $this->appendTemplateAccessMeta($result, $canUseTemplate);
            }

            Log::info($result);
        } catch (\Exception $e) {
            Log::error("Call API Postmark - Get Template: {$e->getMessage()}");
        }

        return [];
    }

    public function storePostmarkTemplate(array $attributes)
    {
        try {
            $result = $this->httpClient->post('templates', array_filter($attributes), 'post');

            if ($result && isset($result['Name'])) {
                $templateId = (int) ($result['TemplateId'] ?? 0);

                /* save template to authorized company */
                if (! auth()->user()->isSysAdmin() && $templateId > 0) {
                    $company = auth()->user()->company;
                    $templates = json_decode($company->templates, true) ?? [];
                    if (! in_array($templateId, $templates)) {
                        $templates[] = $templateId;
                    }
                    $company->templates = json_encode($templates);
                    $company->save();
                }
                /* end */
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Call API Postmark - Store Template: {$e->getMessage()}");

            return [
                'error' => $e->getMessage(),
            ];
        }

        return [];
    }

    public function updatePostmarkTemplate(int $templateId, array $attributes)
    {
        if (! $this->canUseTemplate($templateId)) {
            return [];
        }

        try {
            $datas = [
                'Name' => $attributes['name'],
                'Subject' => $attributes['subject'],
                'TextBody' => Helper::convertHtmlToPlainText($attributes['html_body']),
                'HtmlBody' => $attributes['html_body'],
                'Alias' => $attributes['alias'] ?? null,
            ];

            $result = $this->httpClient->post("templates/{$templateId}", array_filter($datas), 'put');

            if ($result && isset($result['Name'])) {
                $this->getPostmarkTemplate($templateId, true);

                return $result;
            }

            Log::info($result);
        } catch (\Exception $e) {
            Log::error("Call API Postmark - Update Template: {$e->getMessage()}");
        }

        return [];
    }

    public function sendTestPostmarkTemplate(int $templateId, array $attributes)
    {
        if (! $this->canUseTemplate($templateId)) {
            return [];
        }

        try {
            $datas = [
                'From' => $attributes['from_mail'],
                'To' => $attributes['to_mail'],
                'TemplateId' => $templateId,
                'TemplateModel' => $attributes['fields'],
                'InlineCss' => true,
                'Cc' => $attributes['cc'],
                'Bcc' => $attributes['bcc'],
                'Tag' => "TEST-{$templateId}",
                // 'ReplyTo' => $attributes['from_mail'],
                'TrackOpens' => true,
                'TrackLinks' => 'None',
                'Metadata' => [
                    'color' => 'blue',
                    'client-id' => '12345',
                ],
                'MessageStream' => env('POSTMARK_MESSAGE_STREAM', 'outbound'),
            ];

            $result = $this->httpClient->post('email/withTemplate', $datas);

            if ($result && (isset($result['Message']) && $result['Message'] == 'OK')) {
                return $result;
            }

            Log::info($result);
        } catch (\Exception $e) {
            Log::error("Call API Postmark - Send Test with Template: {$e->getMessage()}");
        }

        return [];
    }

    public function upsert(string $templatId, array $attributes)
    {
        EmailTemplate::updateOrCreate(
            [
                'uuid' => $templatId,
            ],
            $attributes
        );

        return true;
    }

    private function getPaidTemplateCatalog(): array
    {
        if ($this->paidTemplateCatalogCache !== null) {
            return $this->paidTemplateCatalogCache;
        }

        $catalog = [];
        foreach ((array) config('postmark_paid_templates.templates', []) as $rawTemplateId => $meta) {
            $templateId = (int) $rawTemplateId;
            if ($templateId <= 0 || ! is_array($meta)) {
                continue;
            }

            $normalized = $this->normalizePaidTemplateMeta($meta, $templateId);
            if (! $normalized['is_paid_locked']) {
                continue;
            }

            $catalog[$templateId] = $normalized;
        }

        foreach ($this->getStoredPaidTemplateOverrides() as $templateId => $override) {
            if (! $override['is_paid_locked']) {
                unset($catalog[$templateId]);

                continue;
            }

            $catalog[$templateId] = array_merge($catalog[$templateId] ?? [], $override);
            if (empty($catalog[$templateId]['credit'])) {
                $catalog[$templateId]['credit'] = trim(implode(' - ', array_filter([
                    $catalog[$templateId]['event_name'] ?? null,
                    $catalog[$templateId]['event_time'] ?? null,
                ])));
            }
        }

        $this->paidTemplateCatalogCache = $catalog;

        return $catalog;
    }

    private function getStoredPaidTemplateOverrides(): array
    {
        if ($this->paidTemplateOverridesCache !== null) {
            return $this->paidTemplateOverridesCache;
        }

        $overrides = [];
        $rows = EmailTemplate::query()
            ->whereNotNull('uuid')
            ->whereNotNull('texts')
            ->get(['uuid', 'texts']);

        foreach ($rows as $row) {
            $templateId = (int) $row->uuid;
            if ($templateId <= 0) {
                continue;
            }

            $paidAccess = data_get($row->texts, 'paid_access');
            if (! is_array($paidAccess) || ! array_key_exists('is_paid_locked', $paidAccess)) {
                continue;
            }

            $overrides[$templateId] = $this->normalizePaidTemplateMeta($paidAccess, $templateId);
        }

        $this->paidTemplateOverridesCache = $overrides;

        return $overrides;
    }

    private function appendTemplateAccessMeta(array $template, bool $canUseTemplate): array
    {
        $templateId = (int) ($template['TemplateId'] ?? 0);
        if ($templateId <= 0) {
            return $template;
        }

        $paidTemplateMeta = $this->getPaidTemplateMeta($templateId);
        $isPaidTemplate = $paidTemplateMeta !== null;
        $isTemplateLocked = $isPaidTemplate && ! $canUseTemplate;
        $canPreviewLockedContent = $isTemplateLocked && $this->canPreviewLockedTemplateContent();

        $template['is_paid_template'] = $isPaidTemplate;
        $template['is_template_locked'] = $isTemplateLocked;
        $template['can_use_template'] = $canUseTemplate;
        $template['can_preview_locked_content'] = $canPreviewLockedContent;

        if ($isPaidTemplate) {
            $template['paid_template'] = $paidTemplateMeta;
        }

        if ($isTemplateLocked) {
            $template['contact_unlock_url'] = $this->getUnlockContactUrl(
                $templateId,
                (string) ($template['Name'] ?? '')
            );
        }

        return $template;
    }

    private function normalizePaidTemplateMeta(array $meta, int $templateId): array
    {
        $eventName = trim((string) ($meta['event_name'] ?? ''));
        $eventTime = trim((string) ($meta['event_time'] ?? ''));
        $credit = trim((string) ($meta['credit'] ?? ''));
        if ($credit === '') {
            $credit = trim(implode(' - ', array_filter([
                $eventName ?: null,
                $eventTime ?: null,
            ])));
        }

        return [
            'template_id' => $templateId,
            'is_paid_locked' => $this->toBoolean($meta['is_paid_locked'] ?? false),
            'event_name' => $eventName,
            'event_time' => $eventTime,
            'credit' => $credit,
        ];
    }

    private function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
        }

        return false;
    }

    private function canPreviewLockedTemplateContent(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $user = Auth::user();
        if (! $user) {
            return false;
        }

        if ($user->isSysAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->hasRole(Role::ROLE_USER);
    }
}
