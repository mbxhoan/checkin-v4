<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class LuckyDrawLayout
 *
 * @property int $id
 * @property int $lucky_draw_id
 * @property int|null $reward_id
 * @property string $name
 * @property int $canvas_width
 * @property int $canvas_height
 * @property string $background_type
 * @property string|null $background_value
 * @property array $blocks
 * @property array|null $settings
 * @property bool $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @property LuckyDraw $luckyDraw
 * @property LuckyDrawReward|null $reward
 *
 * @package App\Models
 */
class LuckyDrawLayout extends BaseModel
{
    protected $table = 'lucky_draw_layouts';

    const BACKGROUND_COLOR = 'color';
    const BACKGROUND_IMAGE = 'image';
    const BACKGROUND_VIDEO = 'video';

    const BLOCK_TYPE_TEXT = 'text';
    const BLOCK_TYPE_IMAGE = 'image';
    const BLOCK_TYPE_AVATAR = 'avatar';
    const BLOCK_TYPE_RANDOM_FIELD = 'random_field';
    const BLOCK_TYPE_RESULT_FIELD = 'result_field';

    const BLOCK_TYPES = [
        self::BLOCK_TYPE_TEXT,
        self::BLOCK_TYPE_IMAGE,
        self::BLOCK_TYPE_AVATAR,
        self::BLOCK_TYPE_RANDOM_FIELD,
        self::BLOCK_TYPE_RESULT_FIELD,
    ];

    const VISIBILITY_ALWAYS = 'always';
    const VISIBILITY_SPINNING = 'spinning';
    const VISIBILITY_SLOWING = 'slowing';
    const VISIBILITY_RESULT = 'result';

    const VISIBILITIES = [
        self::VISIBILITY_ALWAYS,
        self::VISIBILITY_SPINNING,
        self::VISIBILITY_SLOWING,
        self::VISIBILITY_RESULT,
    ];

    protected $casts = [
        'lucky_draw_id' => 'int',
        'reward_id' => 'int',
        'canvas_width' => 'int',
        'canvas_height' => 'int',
        'blocks' => 'json',
        'settings' => 'json',
        'is_active' => 'bool',
    ];

    protected $fillable = [
        'lucky_draw_id',
        'reward_id',
        'name',
        'canvas_width',
        'canvas_height',
        'background_type',
        'background_value',
        'blocks',
        'settings',
        'is_active',
    ];

    public function luckyDraw(): BelongsTo
    {
        return $this->belongsTo(LuckyDraw::class);
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(LuckyDrawReward::class, 'reward_id');
    }

    /**
     * Get default blocks for a new layout
     */
    public function getDefaultBlocks(): array
    {
        return [
            [
                'id' => 'default_name',
                'type' => self::BLOCK_TYPE_RESULT_FIELD,
                'source' => 'name',
                'x' => 960,
                'y' => 540,
                'width' => 800,
                'height' => 100,
                'rotation' => 0,
                'style' => [
                    'fontSize' => 72,
                    'fontFamily' => 'Arial',
                    'fontWeight' => 'bold',
                    'color' => '#FFFFFF',
                    'align' => 'center',
                ],
                'animation' => ['type' => 'fade', 'duration' => 500],
                'visibleWhen' => self::VISIBILITY_RESULT,
                'zIndex' => 1,
                'locked' => false,
            ],
        ];
    }

    /**
     * Check if this is a default layout (not linked to a specific reward)
     */
    public function isDefault(): bool
    {
        return is_null($this->reward_id);
    }
}
