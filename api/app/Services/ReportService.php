<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReportService
{
    public function __construct(
        private readonly CheckinService $checkinService
    ) {}

    public function summary(Event $event): array
    {
        return $this->checkinService->stats($event);
    }

    public function checkins(Event $event, array $filters = []): LengthAwarePaginator
    {
        return $this->checkinService->paginateByEvent($event, $filters);
    }
}
