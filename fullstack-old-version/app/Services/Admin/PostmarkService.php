<?php

namespace App\Services\Admin;

use App\Models\WebhookPostmark;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class PostmarkService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(WebhookPostmark::class);
    }

    public function countByStatus(array $messageIds)
    {
        if (empty($messageIds)) {
            return [];
        }

        $statuses = [];
        $messages = $this->getMessages($messageIds);

        foreach ($messages as $detail) {
            if (in_array($detail->status, [
                "SubscriptionChange"
            ])) continue;

            if (!isset($statuses[$detail->status])) $statuses[$detail->status] = 0;
            $statuses[$detail->status] += 1;
        }

        return $statuses;
    }

    public function getMessages(?array $messageIds = [], ?string $selectRaw = null, ?array $customGroupBy = [], ?array $attributes = [])
    {
        $query = DB::table('webhook_postmarks');

        if (count($messageIds)) {
            $query->whereIn('message_id', $messageIds);
        }

        if (empty($selectRaw)) {
            $selectRaw =
            '
            webhook_postmarks.email,
            webhook_postmarks.status,
            count(*) as total_webhook';
        }

        if (!count($customGroupBy)) {
            $customGroupBy = [
                // 'webhook_postmarks.message_id',
                'webhook_postmarks.email',
                'webhook_postmarks.status',
            ];
        }

        $query->selectRaw($selectRaw);

        if (count($attributes)) {
            foreach ($attributes as $attrCol => $attrValue) {
                if (is_array($attrValue)) {
                   $query->whereIn($attrCol, $attrValue);
                } else {
                   $query->where($attrCol, $attrValue);
                }
            }
        }

        return $query->groupBy($customGroupBy)
            ->orderBy('total_webhook', 'DESC')
            ->get();
    }

    public function getStatusesSummaryByPeriod(string $period = 'month', ?array $attributes = [], ?array $excludeAttributes = [])
    {
        $query = DB::table('webhook_postmarks');

        // Choose grouping by period
        switch ($period) {
            case 'date':
                $dateSelect = "DATE(created_at) as period";
                $groupBy = DB::raw("DATE(created_at)");
                break;

            case 'week':
                $dateSelect = "YEARWEEK(created_at, 1) as period"; // ISO week
                $groupBy = DB::raw("YEARWEEK(created_at, 1)");
                break;

            case 'month':
            default:
                $dateSelect = "DATE_FORMAT(created_at, '%Y-%m') as period";
                $groupBy = DB::raw("DATE_FORMAT(created_at, '%Y-%m')");
                break;
        }

        // Pivot-like aggregation
        $query->selectRaw("
            $dateSelect,
            SUM(CASE WHEN status = 'Delivery' THEN 1 ELSE 0 END) as delivered,
            SUM(CASE WHEN status = 'Open' THEN 1 ELSE 0 END) as opened,
            SUM(CASE WHEN status = 'Bounce' THEN 1 ELSE 0 END) as bounced,
            SUM(CASE WHEN status = 'Click' THEN 1 ELSE 0 END) as clicked,
            SUM(CASE WHEN status = 'SpamComplaint' THEN 1 ELSE 0 END) as spam,
            COUNT(*) as total
        ");

        // Extra filters (optional)
        if (count($attributes)) {
            foreach ($attributes as $col => $val) {
                if (is_array($val)) {
                    $query->whereIn("webhook_postmarks.{$col}", $val);
                } else {
                    $query->where("webhook_postmarks.{$col}", $val);
                }
            }
        }

        if (count($excludeAttributes)) {
            foreach ($excludeAttributes as $col => $val) {
                if (is_array($val)) {
                    $query->whereNotIn("webhook_postmarks.{$col}", $val);
                } else {
                    $query->where("webhook_postmarks.{$col}", '!=', $val);
                }
            }
        }

        $query->groupBy($groupBy)
            ->orderBy('period', 'ASC');

        return $query->get();
    }
}
