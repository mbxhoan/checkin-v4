<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignDetail;
use App\Services\Admin\CampaignDetailService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CampaignDetailController extends Controller
{
    public function __construct(CampaignDetailService $service)
    {
        $this->service = $service;
    }

    public function viewEmail(CampaignDetail $campaign_detail) {}

    public function sendMail(Request $request, Campaign $campaign)
    {
        /* validate confirm */
        $request->validate([
            'confirm' => ['required', 'string', 'max:20', 'in:SEND'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        if ($request->has('scheduled_at')) {
            $scheduledAt = $request->input('scheduled_at');
            $campaign->update([
                'scheduled_at' => ! empty($scheduledAt)
                    ? Carbon::parse($scheduledAt)->format('Y-m-d H:i:s')
                    : null,
            ]);
            $campaign->refresh();
        }

        try {
            $result = $this->service->setupEmails($campaign);
        } catch (\Throwable $e) {
            Log::error($e);
            $msg = auth()->user()->isSysAdmin()
                ? __('campaigns.queue.setup_failed_admin', ['error' => $e->getMessage()])
                : __('campaigns.queue.setup_failed_user');

            return back()->withErrors($msg);
        }

        if (! empty($result['status'])) {
            return redirect()->route('admin.campaigns.edit', $campaign)
                ->withSuccess($result['message'] ?? __('campaigns.queue.default_success'));
        }

        return back()->withErrors($result['message'] ?? __('campaigns.queue.default_failed'));
    }
}
