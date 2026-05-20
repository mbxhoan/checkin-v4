<?php

namespace App\Console\Commands\Email;

use App\Models\Campaign;
use App\Models\Email;
use App\Services\Middleware\EmailService;
use App\Traits\SendMail;
use Illuminate\Console\Command;

class SendEmail extends Command
{
    use SendMail;

    protected $modelCampaign;

    protected $limit;

    protected $holdEachMail;

    protected $campaignId;

    protected $emailId;

    protected $customLimit;

    protected $options;

    protected $service;

    protected $touchedCampaignIds = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:mail {--campaignId=} {--emailId=} {--limit=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Email to clients';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function __construct(EmailService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle()
    {
        $this->options = (object) $this->options();
        $this->campaignId = is_numeric($this->options->campaignId) ? $this->options->campaignId : 0;
        $this->emailId = is_numeric($this->options->emailId) ? $this->options->emailId : 0;
        $this->customLimit = is_numeric($this->options->limit) ? $this->options->limit : 15;

        $this->info('SendEmail started');
        $this->line("Options: campaignId={$this->campaignId}, emailId={$this->emailId}, limit={$this->customLimit}");

        if ($this->emailId) {
            $email = $this->service->findById($this->emailId);

            if (! $email) {
                $this->error("Email not found: {$this->emailId}");
            } elseif ($email->status == Email::STATUS_WAITING) {
                $this->holdEachMail = $email->campaign->hold_time ?? 1;
                $this->comment("Single email mode. Hold time: {$this->holdEachMail}s");
                $this->sendSingleEmail($email);
            } else {
                $this->comment("Email {$this->emailId} status={$email->status}, skipped");
            }
        } else {
            if ($this->campaignId) {
                $campaign = $this->service->findById($this->campaignId);

                if ($campaign) {
                    $this->limit = $campaign->limitation_per_time ?? ($this->customLimit ?? 15);
                    $this->holdEachMail = $campaign->hold_time ?? 5;
                    $this->comment("Campaign mode. Campaign={$campaign->id}, limit={$this->limit}, hold={$this->holdEachMail}s");

                    $emails = Email::query()
                        ->where('campaign_id', $campaign->id)
                        ->where('status', Email::STATUS_WAITING)
                        ->whereNull('message_id')
                        ->whereHas('campaign', function ($query) {
                            $query->whereNull('scheduled_at')
                                ->orWhere('scheduled_at', '<=', now());
                        })
                        ->orderBy('id', 'asc')
                        ->limit((int) ($this->limit ?? 5))
                        ->get();

                    if ($emails->count()) {
                        $emails->load('campaign');
                        $this->dispatchGroupMail($emails);
                    } else {
                        $waitingCount = Email::query()
                            ->where('campaign_id', $campaign->id)
                            ->where('status', Email::STATUS_WAITING)
                            ->whereNull('message_id')
                            ->count();

                        if ($waitingCount > 0 && ! empty($campaign->scheduled_at) && $campaign->scheduled_at->isFuture()) {
                            $this->comment("Campaign {$this->campaignId} has {$waitingCount} queued email(s), scheduled at {$campaign->scheduled_at->format('Y-m-d H:i:s')}");
                        } else {
                            $this->error("Tìm thấy 0 email gửi trên campaign {$this->campaignId}");
                        }
                    }
                } else {
                    $this->error("Campaign not found: {$this->campaignId}");
                }
            } else {
                $this->limit = $this->customLimit;
                $this->comment("Global mode. limit={$this->limit}");
                $emails = Email::query()
                    ->where('status', Email::STATUS_WAITING)
                    ->whereNull('message_id')
                    ->whereHas('campaign', function ($query) {
                        $query->whereNull('scheduled_at')
                            ->orWhere('scheduled_at', '<=', now());
                    })
                    ->orderBy('id', 'asc')
                    ->limit((int) $this->customLimit)
                    ->get();

                if ($emails->count()) {
                    $emails->load('campaign');
                    $this->dispatchGroupMail($emails);
                } else {
                    $this->error('Tìm thấy 0 email gửi');
                }
            }
        }

        $this->info('SendEmail finished');

        return Command::SUCCESS;
    }

    private function dispatchGroupMail($emails)
    {
        $limitLabel = $this->limit ?? $this->customLimit ?? $emails->count();
        $total = $emails->count();
        $this->line("LIMIT: {$limitLabel}");
        $this->info("Found {$total} email(s) to process");
        $claimedCount = 0;
        $skippedCount = 0;
        $index = 1;
        foreach ($emails as $email) {
            $this->comment("Processing {$index}/{$total} - email_id={$email->id}, to={$email->to_email}");
            $claimed = Email::where('id', $email->id)
                ->whereNull('message_id')
                ->where('status', Email::STATUS_WAITING)
                ->update([
                    'message_id' => 1,
                ]);

            if (! $claimed) {
                $skippedCount++;
                $this->comment("Skipped email_id={$email->id} (already claimed)");
                $index++;

                continue;
            }

            $claimedCount++;
            $this->holdEachMail = (int) ($email->campaign->hold_time ?? $this->holdEachMail ?? 0);
            $this->line("Hold time: {$this->holdEachMail}s");
            $this->sendSingleEmail($email);
            $this->touchedCampaignIds[$email->campaign_id] = (int) $email->campaign_id;

            if (! $this->emailId && $this->holdEachMail) {
                $this->comment("Sleeping {$this->holdEachMail}s before next email");
                sleep($this->holdEachMail);
            }

            $index++;
        }

        $this->info("Dispatch done. claimed={$claimedCount}, skipped={$skippedCount}");
        $this->finalizeCampaignStatuses();

        return true;
    }

    private function sendSingleEmail($email)
    {
        $this->line("Start send email_id={$email->id}, campaign_id={$email->campaign_id}");
        if ($email->status == Email::STATUS_SENT) {
            $this->error("Email {$email->to_email} has been sent");

            return;
        }

        $this->line("Preparing to send: {$email->to_email}. *** WAIT FOR {$this->holdEachMail} seconds ***");

        /* check to_email column */
        if (! $email->to_email) {
            $email->update([
                'error_log' => [
                    'error' => 'NO_EMAIL',
                    'message' => 'No email found...',
                ],
                'status' => Email::STATUS_NEW,
            ]);

            $this->error('No email found...');

            return;
        }

        /* validate mail form */
        if (! Email::checkEmailForm($email->to_email)) {
            $email->update([
                'error_log' => [
                    'error' => 'INVALID_EMAIL',
                    'message' => "Email: {$email->to_email} NOT IN correct format!",
                ],
                'status' => Email::STATUS_NEW,
            ]);

            $this->error("Email: {$email->to_email} NOT IN correct format!");

            return;
        }

        /* validate limit send mail */
        $campaign = $email->campaign;
        $event = $campaign->event;
        $company = $event->company;
        if (! empty($company->limited_emails) && $company->limited_emails > 0) {
            $sentEmailCount = Email::whereNotNull('sent_at')
                ->whereHas('campaign.event', function ($query) use ($company) {
                    $query->where('company_id', $company->id);
                })
                ->count();

            if ($sentEmailCount >= $company->limited_emails) {
                $this->comment("Limit reached: {$sentEmailCount}/{$company->limited_emails}");
                $email->update([
                    'error_log' => [
                        'error' => 'LIMIT_EXCEEDED',
                        'message' => "Đã dùng {$sentEmailCount}/{$company->limited_emails} email. Vui lòng nâng cấp gói hoặc tăng giới hạn để tiếp tục gửi.",
                        'sent' => $sentEmailCount,
                        'limit' => (int) $company->limited_emails,
                    ],
                    'status' => Email::STATUS_NEW,
                ]);

                return;
            }
        }

        if ($email->is_online) {
            $this->comment('Sending via Postmark template (online)');
            $emailSend = $this->sendSingleEmailByPostmark($email);
        } else {
            $this->comment('Sending via Postmark template (offline)');
            $emailSend = $this->sendSingleOfflineByPostmark($email);
        }

        if ((int) $emailSend == 202) {
            $this->info("Sent to: {$email->to_email}");

            $email->update([
                'sent_at' => date('Y-m-d H:i:s'),
                'status' => Email::STATUS_SENT,
            ]);
        } elseif ($emailSend == 1) {
            $this->info("Sent to: {$email->to_email}");

            $email->update([
                'sent_at' => date('Y-m-d H:i:s'),
                'status' => Email::STATUS_SENT,
            ]);
        } else {
            $this->error("PASSED: {$email->to_email}");
            $email->update([
                // 'error_log' => "PASSED: {$email->to_email}",
                'status' => Email::STATUS_NEW,
            ]);
        }

        if (! $this->holdEachMail) {
            $this->holdEachMail = $email->campaign->hold_time ?? 5;
        }

        $this->line("Finish send email_id={$email->id}, status={$email->status}");

        return $emailSend;
    }

    private function finalizeCampaignStatuses(): void
    {
        if (! count($this->touchedCampaignIds)) {
            return;
        }

        foreach (array_values(array_unique($this->touchedCampaignIds)) as $campaignId) {
            $waitingCount = Email::query()
                ->where('campaign_id', $campaignId)
                ->where('status', Email::STATUS_WAITING)
                ->count();

            if ($waitingCount <= 0) {
                Campaign::query()
                    ->where('id', $campaignId)
                    ->update(['status' => Campaign::STATUS_COMPLETED]);
            } else {
                Campaign::query()
                    ->where('id', $campaignId)
                    ->update(['status' => Campaign::STATUS_SENDING]);
            }
        }
    }
}
