<?php

namespace App\Services\Admin;

use App\Models\CustomFieldTemplate;
use App\Models\Event;
use App\Models\LuckyDraw;

class LuckyDrawFieldService
{
    /**
     * Get all available fields for a lucky draw's event
     */
    public function getAvailableFields(LuckyDraw $luckyDraw): array
    {
        $event = $luckyDraw->event;

        return [
            'core_fields' => $this->getCoreFields(),
            'custom_fields' => $event ? $this->getCustomFields($event) : [],
            'computed_fields' => $this->getComputedFields(),
        ];
    }

    /**
     * Core client fields (always available)
     */
    protected function getCoreFields(): array
    {
        return [
            ['key' => 'id', 'label' => 'ID', 'type' => 'number'],
            ['key' => 'qrcode', 'label' => 'QR Code', 'type' => 'text'],
            ['key' => 'name', 'label' => 'Name', 'type' => 'text'],
            ['key' => 'email', 'label' => 'Email', 'type' => 'email'],
            ['key' => 'phone', 'label' => 'Phone', 'type' => 'tel'],
            ['key' => 'type', 'label' => 'Type', 'type' => 'select'],
            ['key' => 'status', 'label' => 'Status', 'type' => 'select'],
        ];
    }

    /**
     * Custom fields from CustomFieldTemplate
     */
    protected function getCustomFields(Event $event): array
    {
        $templates = CustomFieldTemplate::where('event_id', $event->id)->get();

        return $templates->map(function (CustomFieldTemplate $template) {
            return [
                'key' => "custom_fields.{$template->name}",
                'label' => $template->description ?? $template->name,
                'type' => $this->mapFieldType($template->type),
                'options' => $template->getOptionsAsArray(),
            ];
        })->toArray();
    }

    /**
     * Computed/derived fields
     */
    protected function getComputedFields(): array
    {
        return [
            ['key' => 'phone_last4', 'label' => 'Phone (last 4)', 'type' => 'text'],
            ['key' => 'checked_in_at', 'label' => 'Check-in Time', 'type' => 'datetime'],
        ];
    }

    /**
     * Map CustomFieldTemplate type to simple type
     */
    protected function mapFieldType(string $type): string
    {
        return match ($type) {
            CustomFieldTemplate::TYPE_NUMBER => 'number',
            CustomFieldTemplate::TYPE_EMAIL => 'email',
            CustomFieldTemplate::TYPE_TEL => 'tel',
            CustomFieldTemplate::TYPE_SELECT,
            CustomFieldTemplate::TYPE_RADIO,
            CustomFieldTemplate::TYPE_MULTICHOICE => 'select',
            CustomFieldTemplate::TYPE_AVATAR,
            CustomFieldTemplate::TYPE_IMAGE => 'image',
            default => 'text',
        };
    }

    /**
     * Resolve field value from client data
     */
    public function resolveFieldValue(array $clientData, string $fieldKey): mixed
    {
        if (str_starts_with($fieldKey, 'custom_fields.')) {
            $customKey = str_replace('custom_fields.', '', $fieldKey);
            return data_get($clientData, "custom_fields.{$customKey}");
        }

        if ($fieldKey === 'phone_last4') {
            return substr($clientData['phone'] ?? '', -4);
        }

        return data_get($clientData, $fieldKey);
    }

    /**
     * Get all valid field keys as flat array
     */
    public function getValidFieldKeys(LuckyDraw $luckyDraw): array
    {
        $availableFields = $this->getAvailableFields($luckyDraw);
        $keys = [];

        foreach ($availableFields as $group) {
            foreach ($group as $field) {
                $keys[] = $field['key'];
            }
        }

        return $keys;
    }
}
