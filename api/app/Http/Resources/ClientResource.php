<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
            'source' => $this->source?->value,
            'custom_fields' => $this->custom_fields,
            'registered_at' => $this->registered_at?->toISOString(),
            'checked_in_at' => $this->checked_in_at?->toISOString(),
            'checked_out_at' => $this->checked_out_at?->toISOString(),
            'event' => new EventResource($this->whenLoaded('event')),
            'company' => new CompanyResource($this->whenLoaded('company')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
