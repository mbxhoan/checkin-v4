<?php
namespace App\Services\Admin;

use App\Helpers\Helper;
use App\HttpClient\HttpClient;
use App\Services\BaseService;
use Illuminate\Support\Facades\Log;

class EmailSenderService extends BaseService
{
    public function __construct()
    {
        $this->httpClient = new HttpClient(env("POSTMARK_API_URL"), [
            "Accept"                    => "application/json",
            "X-Postmark-Account-Token"  => env("POSTMARK_ACCOUNT_TOKEN"),
        ]);
    }

    public function client()
    {
        return app(ClientService::class);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function company()
    {
        return app(CompanyService::class);
    }

    public function campaign_detail()
    {
        return app(CampaignDetailService::class);
    }

    public function email_template()
    {
        return app(EmailTemplateService::class);
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

    public function middleware_client()
    {
        // return app(MiddlewareClientService::class);
    }

    public function getAuthorizedPostmarkSenderIds()
    {
        if (!auth()->user()->isSysAdmin()) {
            $postmarkSenderIds = auth()->user()->company->senders;
            if ($postmarkSenderIds) $postmarkSenderIds = json_decode($postmarkSenderIds, true);
        }

        return $postmarkSenderIds ?? [];
    }

    public function getAuthorizedPostmarkSenders(array $senders = [])
    {
        $authorizedPostmarkSenderIds = $this->getAuthorizedPostmarkSenderIds();

        if (count($authorizedPostmarkSenderIds)) {
            if (count($senders)) {
                foreach ($senders as $index => $sender) {
                    if (!in_array($sender['ID'], $authorizedPostmarkSenderIds)) {
                        unset($senders[$index]);
                    }
                }
            }
        } else {
            if (!auth()->user()->isSysAdmin()) {
                return [];
            }
        }

        return $senders;
    }

    public function getPostmarkSenders(bool $updateToRedis = false)
    {
        $senders = $this->getRedis("postmark", "email_senders", "array");

        if (!$updateToRedis) {
            if (count($senders)) {
                /* authorize senders */
                $senders = $this->getAuthorizedPostmarkSenders($senders);
                return [
                    'SenderSignatures'  => $senders,
                    'TotalCount'        => count($senders)
                ];
            }
        }

        try {
            $params = [
                'count'             => 100,
                'offset'            => 0,
            ];

            $result = $this->httpClient->get("senders", $params);

            if ($result && (isset($result['TotalCount']) && isset($result['SenderSignatures']))) {
                /* authorize senders */
                $senders = $this->getAuthorizedPostmarkSenders($result['SenderSignatures']);
                $result['SenderSignatures'] = $senders;
                $result['TotalCount'] = count($senders);

                /* re-assign senders */
                $this->updateRedis("postmark", "email_senders", json_encode($result['SenderSignatures']), config("app.times.minutes.30"));
                return $result;
            }

            Log::info($result);
        } catch (\Exception $e) {
            Log::error("Call API Postmark - Get Senders: {$e}");
        }

        return [];
    }

    public function getPostmarkSender(int $senderId, bool $updateToRedis = false)
    {
        if (!auth()->user()->isSysAdmin()) {
            $authorizedPostmarkTemplateIds = $this->getAuthorizedPostmarkSenderIds();

            if (!count($authorizedPostmarkTemplateIds) || !in_array($senderId, $authorizedPostmarkTemplateIds)) {
                return [];
            }
        }

        $sender = $this->getRedis("postmark", "email_sender-{$senderId}", "array");

        if (!$updateToRedis) {
            if (count($sender)) {
                return $sender;
            }
        }

        try {
            $result = $this->httpClient->get("senders/{$senderId}");

            if ($result && (isset($result['ID']))) {
                $this->updateRedis("postmark", "email_sender-{$senderId}", json_encode($result), config("app.times.minutes.30"));
                return $result;
            }

            Log::info($result);
        } catch (\Exception $e) {
            Log::error("Call API Postmark - Get Sender: {$e}");
        }

        return [];
    }

    public function updatePostmarkSender(int $senderId, array $attributes)
    {
        $sender = $this->getRedis("postmark", "email_sender-{$senderId}", "array");

        try {
            $result = $this->httpClient->post("senders/{$senderId}", [
                "Name"              => $attributes['name'],
                // "ReplyToEmail"      => $sender['ReplyToEmailAddress'],
                // "ReturnPathDomain"  => $sender['ReturnPathDomain']
            ], "put");

            if ($result && count($result)) {
                $this->getPostmarkSenders(true);
                $this->updateRedis("postmark", "email_sender-{$senderId}", json_encode($result), config("app.times.minutes.30"));
                return $result;
            }

            Log::info($result);
        } catch (\Exception $e) {
            Log::error("Call API Postmark - Edit Sender: {$e}");
        }

        return [];
    }

    /**
     * Create a sender signature on Postmark.
     *
     * Returns the Postmark response array on success, or [] on failure.
     */
    public function createPostmarkSender(array $attributes): array
    {
        try {
            if (empty(env('POSTMARK_API_URL')) || empty(env('POSTMARK_ACCOUNT_TOKEN'))) {
                return [
                    'Message' => 'Chua cau hinh Postmark (POSTMARK_API_URL / POSTMARK_ACCOUNT_TOKEN). Vui long kiem tra file .env.',
                ];
            }

            $payload = [
                // Postmark Sender Signatures API fields
                'FromEmail' => $attributes['from_email'],
                'Name'      => $attributes['from_name'],
            ];

            if (!empty($attributes['reply_to'])) {
                $payload['ReplyToEmail'] = $attributes['reply_to'];
            }

            if (!empty($attributes['personal_note'])) {
                // This note shows up in the confirmation email (Postmark branding)
                $payload['ConfirmationPersonalNote'] = $attributes['personal_note'];
            }

            $result = $this->httpClient->post('senders', $payload);

            if ($result && isset($result['ID'])) {
                // Refresh cached sender list so the new sender is visible right away.
                $this->getPostmarkSenders(true);
                $this->updateRedis(
                    "postmark",
                    "email_sender-{$result['ID']}",
                    json_encode($result),
                    config("app.times.minutes.30")
                );
                return $result;
            }

            // Return error body (if any) so the UI can display a useful message.
            if (is_array($result) && count($result)) {
                Log::info($result);
                return $result;
            }
        } catch (\Exception $e) {
            Log::error("Call API Postmark - Create Sender: {$e}");
        }

        return [];
    }
}
