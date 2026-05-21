<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'logo' => $this->logo,
            'status' => $this->status?->value,
            'settings' => $this->settings,
            'max_events' => $this->max_events,
            'max_users' => $this->max_users,
            'subscription_plan' => $this->subscription_plan,
            'subscription_expires_at' => $this->subscription_expires_at?->toISOString(),
            'users_count' => $this->whenCounted('users'),
            'events_count' => $this->whenCounted('events'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
