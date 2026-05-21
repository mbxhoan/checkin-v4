<?php

namespace App\Http\Resources\Legacy;

use App\Enums\CheckinType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LegacyCheckinResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'qrcode' => $this->client?->qrcode,
            'name' => $this->client?->name,
            'checkin_count' => $this->client?->checkins()
                ->where('type', CheckinType::CheckIn)
                ->count(),
            'checkin_at' => $this->scanned_at?->format('Y-m-d H:i:s'),
        ];
    }
}
