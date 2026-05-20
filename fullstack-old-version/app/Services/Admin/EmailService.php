<?php
namespace App\Services\Admin;

use App\Models\Campaign;
use App\Models\Company;
use App\Services\BaseService;
use App\Models\Email;
use App\Services\Middleware\EmailService as MiddlewareEmailService;

class EmailService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Email::class);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function company()
    {
        return app(CompanyService::class);
    }

    public function imp_exp_file()
    {
        return app(ImpexpFileService::class);
    }

    public function mediaLibraryService()
    {
        return new MediaLibraryService($this->attributes);
    }

    public function custom_field_template()
    {
        return app(CustomFieldTemplateService::class);
    }

    public function middleware_email()
    {
        return app(MiddlewareEmailService::class);
    }

    public function closeAllEmailByCampaign(Campaign $campaign)
    {
        $emails = $this->getListByAttributes([
            'campaign_id' => $campaign->id,
        ]);

        foreach ($emails as $email) {
            $this->update($email->id, [
                'status' => Email::STATUS_CLOSED,
            ]);
        }

        return true;
    }

    public function sendMailByJob($email)
    {
        $this->middleware_email()->sendMailByJob($email);
        return $email->refresh();
    }

    public function sendMailNow($email)
    {
        return $this->middleware_email()->sendMailNow($email);
    }

    public function setEmailWaiting(Email $email, string $send = "job")
    {
        return $this->middleware_email()->setEmailWaiting($email, $send);
    }

    /**
     * Email quota helpers (Company.limited_emails).
     * These are used to warn early (before queueing/sending) instead of failing later in the send command.
     */
    public function getCompanyEmailLimit(Company $company): ?int
    {
        $limit = (int) ($company->limited_emails ?? 0);
        return $limit > 0 ? $limit : null;
    }

    public function countSentEmailsForCompany(Company $company): int
    {
        return (int) Email::query()
            ->whereNotNull('sent_at')
            ->whereHas('campaign.event', function ($q) use ($company) {
                $q->where('company_id', $company->id);
            })
            ->count();
    }

    /**
     * Count scheduled (WAITING) emails for the company.
     * Optionally exclude a campaign when you are about to reset/close its current queue.
     */
    public function countWaitingEmailsForCompany(Company $company, ?int $excludeCampaignId = null): int
    {
        $q = Email::query()
            ->where('status', Email::STATUS_WAITING)
            ->whereHas('campaign.event', function ($q) use ($company) {
                $q->where('company_id', $company->id);
            });

        if (!empty($excludeCampaignId)) {
            $q->where('campaign_id', '!=', (int) $excludeCampaignId);
        }

        return (int) $q->count();
    }

    /**
     * Check if we can send $count emails right now (based on already-sent emails).
     */
    public function canSendNowForCompany(Company $company, int $count = 1): array
    {
        $limit = $this->getCompanyEmailLimit($company);
        if ($limit === null) {
            return [
                'ok' => true,
                'limit' => null,
                'sent' => null,
                'waiting' => null,
                'remaining' => null,
                'message' => null,
            ];
        }

        $sent = $this->countSentEmailsForCompany($company);
        $remaining = max(0, $limit - $sent);
        $ok = $count <= $remaining;

        $message = $ok ? null : "Bạn đã dùng {$sent}/{$limit} email. Không thể gửi thêm, vui lòng nâng cấp gói hoặc tăng giới hạn email.";

        return [
            'ok' => $ok,
            'limit' => $limit,
            'sent' => $sent,
            'waiting' => null,
            'remaining' => $remaining,
            'message' => $message,
        ];
    }

    /**
     * Check if we can put $pendingCount emails into the queue (based on sent + current waiting).
     */
    public function canQueueForCompany(Company $company, int $pendingCount, ?int $excludeCampaignId = null): array
    {
        $limit = $this->getCompanyEmailLimit($company);
        if ($limit === null) {
            return [
                'ok' => true,
                'limit' => null,
                'sent' => null,
                'waiting' => null,
                'remaining' => null,
                'message' => null,
            ];
        }

        $sent = $this->countSentEmailsForCompany($company);
        $waiting = $this->countWaitingEmailsForCompany($company, $excludeCampaignId);
        $usedOrQueued = $sent + $waiting;
        $remaining = max(0, $limit - $usedOrQueued);
        $ok = $pendingCount <= $remaining;

        if ($ok) {
            $message = null;
        } else {
            if ($remaining <= 0) {
                $message = "Bạn đã đạt giới hạn {$limit} email (đã gửi: {$sent}, đang chờ: {$waiting}). Vui lòng nâng cấp gói hoặc tăng giới hạn email.";
            } else {
                $message = "Bạn chỉ còn {$remaining} email có thể gửi (giới hạn {$limit}, đã gửi: {$sent}, đang chờ: {$waiting}). Danh sách hiện tại có {$pendingCount} email nên không thể đưa vào hàng đợi.";
            }
        }

        return [
            'ok' => $ok,
            'limit' => $limit,
            'sent' => $sent,
            'waiting' => $waiting,
            'remaining' => $remaining,
            'message' => $message,
        ];
    }
}
