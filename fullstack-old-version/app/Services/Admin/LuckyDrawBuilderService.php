<?php

namespace App\Services\Admin;

use App\Models\LuckyDraw;
use App\Models\LuckyDrawLayout;
use App\Models\LuckyDrawReward;
use App\Services\BaseService;
use Illuminate\Validation\ValidationException;

class LuckyDrawBuilderService extends BaseService
{
    protected LuckyDrawFieldService $fieldService;

    public function __construct(LuckyDrawFieldService $fieldService)
    {
        $this->model = resolve(LuckyDrawLayout::class);
        $this->fieldService = $fieldService;
    }

    /**
     * Create layout for lucky draw (default or per-reward)
     */
    public function createLayout(LuckyDraw $luckyDraw, array $data, ?LuckyDrawReward $reward = null): LuckyDrawLayout
    {
        $blocks = $data['blocks'] ?? [];
        $this->validateBlocks($blocks, $luckyDraw);
        $this->validateRandomFieldCount($blocks, $reward);

        return LuckyDrawLayout::create([
            'lucky_draw_id' => $luckyDraw->id,
            'reward_id' => $reward?->id,
            'name' => $data['name'] ?? ($reward ? $reward->name : 'Default Layout'),
            'canvas_width' => $data['canvas_width'] ?? 1920,
            'canvas_height' => $data['canvas_height'] ?? 1080,
            'background_type' => $data['background_type'] ?? 'color',
            'background_value' => $data['background_value'] ?? '#000000',
            'blocks' => $data['blocks'] ?? [],
            'settings' => $data['settings'] ?? null,
            'is_active' => true,
        ]);
    }

    /**
     * Update layout
     */
    public function updateLayout(LuckyDrawLayout $layout, array $data): LuckyDrawLayout
    {
        $blocks = $data['blocks'] ?? $layout->blocks;
        $this->validateBlocks($blocks, $layout->luckyDraw);

        $reward = $layout->reward_id ? $layout->reward : null;
        $this->validateRandomFieldCount($blocks, $reward);

        $layout->update([
            'name' => $data['name'] ?? $layout->name,
            'canvas_width' => $data['canvas_width'] ?? $layout->canvas_width,
            'canvas_height' => $data['canvas_height'] ?? $layout->canvas_height,
            'background_type' => $data['background_type'] ?? $layout->background_type,
            'background_value' => $data['background_value'] ?? $layout->background_value,
            'blocks' => $data['blocks'] ?? $layout->blocks,
            'settings' => $data['settings'] ?? $layout->settings,
        ]);

        return $layout->fresh();
    }

    /**
     * Validate block configurations
     */
    protected function validateBlocks(array $blocks, LuckyDraw $luckyDraw): void
    {
        $validFieldKeys = $this->fieldService->getValidFieldKeys($luckyDraw);

        foreach ($blocks as $index => $block) {
            // Validate required properties
            if (empty($block['id'])) {
                throw ValidationException::withMessages([
                    "blocks.{$index}.id" => 'Block ID is required',
                ]);
            }

            if (empty($block['type'])) {
                throw ValidationException::withMessages([
                    "blocks.{$index}.type" => 'Block type is required',
                ]);
            }

            // Validate type
            if (!in_array($block['type'], LuckyDrawLayout::BLOCK_TYPES)) {
                throw ValidationException::withMessages([
                    "blocks.{$index}.type" => "Invalid block type: {$block['type']}",
                ]);
            }

            // Validate source field for field-based blocks
            $sourceRequiredTypes = [
                LuckyDrawLayout::BLOCK_TYPE_RANDOM_FIELD,
                LuckyDrawLayout::BLOCK_TYPE_RESULT_FIELD,
                LuckyDrawLayout::BLOCK_TYPE_AVATAR,
            ];

            if (in_array($block['type'], $sourceRequiredTypes)) {
                if (empty($block['source'])) {
                    throw ValidationException::withMessages([
                        "blocks.{$index}.source" => 'Source field is required for this block type',
                    ]);
                }

                if (!in_array($block['source'], $validFieldKeys)) {
                    throw ValidationException::withMessages([
                        "blocks.{$index}.source" => "Invalid source field: {$block['source']}",
                    ]);
                }
            }

            // Validate visibleWhen
            $visibility = $block['visibleWhen'] ?? 'always';
            if (!in_array($visibility, LuckyDrawLayout::VISIBILITIES)) {
                throw ValidationException::withMessages([
                    "blocks.{$index}.visibleWhen" => "Invalid visibility: {$visibility}",
                ]);
            }
        }
    }

    /**
     * Số ô quay ngẫu nhiên (random_field) không được vượt quá value của giải
     */
    protected function validateRandomFieldCount(array $blocks, ?LuckyDrawReward $reward): void
    {
        if ($reward === null) {
            return;
        }

        $maxSlots = (int) $reward->value;
        if ($maxSlots < 1) {
            return;
        }

        $randomFieldCount = collect($blocks)->where('type', LuckyDrawLayout::BLOCK_TYPE_RANDOM_FIELD)->count();
        if ($randomFieldCount > $maxSlots) {
            throw ValidationException::withMessages([
                'blocks' => ["Số lượng ô quay ngẫu nhiên ($randomFieldCount) vượt quá giá trị giải ($maxSlots). Giải này chỉ cho phép tối đa $maxSlots ô."],
            ]);
        }
    }

    /**
     * Generate preview data for layout
     */
    public function generatePreviewData(LuckyDrawLayout $layout, ?int $clientId = null): array
    {
        $luckyDraw = $layout->luckyDraw;
        $client = $clientId
            ? $luckyDraw->clients()->find($clientId)
            : $luckyDraw->clients()->inRandomOrder()->first();

        if (!$client) {
            return [
                'layout' => $layout->toArray(),
                'sample_data' => $this->getSampleData(),
            ];
        }

        $clientData = $client->toArray();
        $resolvedBlocks = collect($layout->blocks)->map(function ($block) use ($clientData) {
            if (isset($block['source'])) {
                $block['resolved_value'] = $this->fieldService->resolveFieldValue(
                    $clientData,
                    $block['source']
                );
            }
            return $block;
        })->toArray();

        return [
            'layout' => array_merge($layout->toArray(), ['blocks' => $resolvedBlocks]),
            'client' => $clientData,
        ];
    }

    /**
     * Sample data for preview when no clients exist
     */
    protected function getSampleData(): array
    {
        return [
            'id' => 1,
            'qrcode' => 'SAMPLE-001',
            'name' => 'Nguyen Van A',
            'email' => 'sample@example.com',
            'phone' => '0901234567',
            'phone_last4' => '4567',
            'type' => 'VIP',
            'custom_fields' => [
                'company' => 'Sample Company',
                'avatar' => '/images/default-avatar.png',
            ],
        ];
    }

    /**
     * Clone layout to another reward
     */
    public function cloneLayout(LuckyDrawLayout $source, ?LuckyDrawReward $targetReward = null): LuckyDrawLayout
    {
        return LuckyDrawLayout::create([
            'lucky_draw_id' => $source->lucky_draw_id,
            'reward_id' => $targetReward?->id,
            'name' => ($targetReward ? $targetReward->name : 'Copy of') . ' ' . $source->name,
            'canvas_width' => $source->canvas_width,
            'canvas_height' => $source->canvas_height,
            'background_type' => $source->background_type,
            'background_value' => $source->background_value,
            'blocks' => $source->blocks,
            'settings' => $source->settings,
            'is_active' => true,
        ]);
    }

    /**
     * Get or create default layout for lucky draw
     */
    public function getOrCreateDefaultLayout(LuckyDraw $luckyDraw): LuckyDrawLayout
    {
        $layout = $luckyDraw->defaultLayout;

        if (!$layout) {
            $layout = $this->createLayout($luckyDraw, [
                'name' => 'Default Layout',
                'blocks' => (new LuckyDrawLayout())->getDefaultBlocks(),
            ]);
        }

        return $layout;
    }
}
