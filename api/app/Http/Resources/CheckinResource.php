<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckinResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_id' => $this->event_id,
            'client_id' => $this->client_id,
            'scanned_by' => $this->scanned_by,
            'type' => $this->type?->value,
            'scanned_at' => $this->scanned_at?->toISOString(),
            'device_info' => $this->device_info,
            'notes' => $this->notes,
            'client' => new ClientResource($this->whenLoaded('client')),
            'scanner' => new UserResource($this->whenLoaded('scanner')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
