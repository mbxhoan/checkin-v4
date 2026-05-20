<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(
            parent::toArray($request),
            [
                'align_in_bootstrap'        => $this->getAlignInBootstrap(),
                'logo'                      => $this->logo ? $this->logoUrl->getUrl() : null,
                'favicon'                   => $this->favicon ? $this->faviconUrl->getUrl() : null,
                'main_bg_desktop'           => $this->main_bg_desktop ? $this->mainBgDesktop->getUrl() : null,
                'main_bg_mobile'            => $this->main_bg_mobile ? $this->mainBgMobile->getUrl() : null,
                'custom_field_templates'    => $this->getCustomFieldTemplates(true, true),
            ]
        );
    }
}
