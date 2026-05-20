<?php

namespace App\Traits;

use App\Helpers\Helper;
use App\Mail\SendMailByPostmark;
use App\Models\Email;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Postmark\PostmarkClient;
use SendGrid\Mail\Mail;
use SendGrid\Mail\To;

trait SendMail
{
    protected array $postmarkReplyToMap = [];

    protected bool $postmarkReplyToMapLoaded = false;

    /**
     * @param object - Array
     * @return mixed
     */
    /* public function sendMail($objects = [])
    {
        if (config('app.env') == 'production') {
            // Use Sendgrid
            return $this->sendWithGridMail($objects);
        } else {
            // Use Mail Trap
            return $this->sendWithGridMail($objects);
            // return $this->sendWithMailTrap($objects);
        }
    } */

    private function sendSingleEmailByPostmark($newMail)
    {
        $newMail->update([
            'error_log' => null,
        ]);

        $newMail->refresh();

        if ($newMail->status != Email::STATUS_WAITING) {
            return false;
        }

        try {
            $sendMail = new PostmarkClient(getenv('POSTMARK_TOKEN'));
            $ccArray = json_decode($newMail->campaign->cc, true);
            $ccEmails = implode(', ', $ccArray);
            $bccArray = json_decode($newMail->campaign->bcc, true);
            $bccEmails = implode(', ', $bccArray);
            $params = json_decode($newMail->param, true);
            // Resolve reply-to from sender signature so each sender uses its own reply mailbox.
            $replyTo = $this->resolvePostmarkReplyTo((string) $newMail->from_email);

            $sendResult = $sendMail->sendEmailWithTemplate(
                $newMail->from_email,
                $newMail->to_email,
                (int) $newMail->template_id,
                $params,
                true,                       //
                $newMail->campaign->type ?? $newMail->campaign->id,   // Tag
                true,                       // Track opens
                $replyTo,                   // Reply to (from Postmark sender signature)
                $ccEmails,                  // CC
                $bccEmails,                 // BCC
                [],                         // Headers
                $attachments ?? []        // Attachments
            );

            $this->info(json_encode($sendResult, JSON_PRETTY_PRINT));
            $statusCode = 202;

            $newMail->update([
                'message_id' => $sendResult->MessageID,
                'server_response' => json_encode($sendResult),
            ]);
        } catch (Exception $e) {
            $this->info('Caught exception: '.$e->getMessage()."\n");
            $newMail->update([
                'status' => Email::STATUS_NEW,
                'error_log' => [
                    'error' => $e->getMessage(),
                ],
            ]);

            return false;
        }

        return $statusCode;
    }

    /* SEND WITH OFFLINE TEMPLATE */
    private function sendSingleOfflineByPostmark($email)
    {
        $email->update([
            'error_log' => null,
        ]);

        if (! Helper::checkTemplateEmail($email->template_id)) {
            $this->error('Template not found');
            $email->update([
                'status' => Email::STATUS_NEW,
                'error_log' => [
                    'error' => 'Template not found',
                ],
            ]);

            return false;
        }

        try {
            $send = new SendMailByPostmark($email->template_id, $email);
            $sendResponse = $send->sendThem();
            // $this->line(print_r($sendResponse));
            $this->info(json_encode($sendResponse, JSON_PRETTY_PRINT));

            // $email->update([
            //     'message_id'        => $sendResult->MessageID,
            //     'server_response'   => $sendResult,
            // ]);

            $email->update([
                'message_id' => $sendResponse->MessageID,
                'server_response' => json_encode($sendResponse),
            ]);
        } catch (Exception $e) {
            $this->info('Caught exception: '.$e->getMessage()."\n");
            $email->update([
                'status' => Email::STATUS_NEW,
                'error_log' => [
                    'error' => $e->getMessage(),
                ],
            ]);

            return false;
        }

        return 1;
    }

    private function resolvePostmarkReplyTo(string $fromEmail): ?string
    {
        $senderEmail = strtolower(trim($fromEmail));
        if ($senderEmail === '') {
            return null;
        }

        if (! $this->postmarkReplyToMapLoaded) {
            $this->postmarkReplyToMap = $this->loadPostmarkReplyToMap();
            $this->postmarkReplyToMapLoaded = true;
        }

        return $this->postmarkReplyToMap[$senderEmail] ?? null;
    }

    private function loadPostmarkReplyToMap(): array
    {
        $accountToken = trim((string) env('POSTMARK_ACCOUNT_TOKEN', ''));
        if ($accountToken === '') {
            return [];
        }

        $cacheKey = 'postmark:sender_reply_to_map';

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($accountToken) {
            $apiUrl = rtrim((string) env('POSTMARK_API_URL', 'https://api.postmarkapp.com'), '/');
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'X-Postmark-Account-Token' => $accountToken,
            ])->timeout(10)->get("{$apiUrl}/senders", [
                'count' => 500,
                'offset' => 0,
            ]);

            if (! $response->successful()) {
                return [];
            }

            $senderSignatures = (array) ($response->json('SenderSignatures') ?? []);
            $replyToMap = [];

            foreach ($senderSignatures as $senderSignature) {
                $emailAddress = strtolower(trim((string) ($senderSignature['EmailAddress'] ?? '')));
                if ($emailAddress === '') {
                    continue;
                }

                $replyToAddress = trim((string) ($senderSignature['ReplyToEmailAddress'] ?? ''));
                if ($replyToAddress !== '') {
                    $replyToMap[$emailAddress] = $replyToAddress;
                }
            }

            return $replyToMap;
        });
    }
}
