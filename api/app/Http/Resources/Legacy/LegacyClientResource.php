<?php

namespace App\Http\Resources\Legacy;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LegacyClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_id' => $this->event_id,
            'company_id' => $this->company_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'qrcode' => $this->qrcode,
            'status' => $this->status?->value,
            'custom_fields' => $this->custom_fields,
            'source' => $this->source?->value,
            'registered_at' => $this->registered_at?->toIso8601String(),
            'checked_in_at' => $this->checked_in_at?->toIso8601String(),
            'checked_out_at' => $this->checked_out_at?->toIso8601String(),
            'event' => $this->whenLoaded('event', fn () => [
                'id' => $this->event->id,
                'company_id' => $this->event->company_id,
                'name' => $this->event->name,
                'code' => $this->event->code,
                'status' => $this->event->status?->value,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
