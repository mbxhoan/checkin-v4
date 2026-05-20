<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Intervention\Image\Facades\Image;

/**
 * Class CardDetail
 *
 * @property int $id
 * @property int $card_id
 * @property string $card_code
 * @property string|null $type
 * @property string|null $img_path
 * @property int|null $pos_x
 * @property int|null $pos_y
 * @property int|null $font_size
 * @property int|null $width
 * @property int|null $height
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Card $card
 *
 * @package App\Models
 */
class CardDetail extends BaseModel
{
	protected $table = 'card_details';

    /* STATUS */

    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_DELETED = 'DELETED';

    const STATUES = [
        self::STATUS_ACTIVE => 'Hiển thị',
        self::STATUS_DELETED => 'Ẩn',
    ];

    /* HORIZONTAL ALIGN */

    const H_ALIGN_LEFT     = 'left';
    const H_ALIGN_CENTER   = 'center';
    const H_ALIGN_RIGHT    = 'right';

    const H_ALIGNS = [
        self::H_ALIGN_LEFT     => 'Trái',
        self::H_ALIGN_CENTER   => 'Giữa',
        self::H_ALIGN_RIGHT    => 'Phải',
    ];

    /* VERTICAL ALIGN */

    const V_ALIGN_TOP       = 'top';
    const V_ALIGN_center    = 'center';
    const V_ALIGN_BOTTOM    = 'bottom';

    const V_ALIGNS = [
        self::V_ALIGN_TOP       => 'Top',
        self::V_ALIGN_center    => 'Middle',
        self::V_ALIGN_BOTTOM    => 'Bottom',
    ];

    /* TYPE */

    const TYPE_NONE     = '';
    const TYPE_FIELD    = 'FIELD';
    const TYPE_QRCODE   = 'QRCODE';
    const TYPE_IMG      = 'IMG';
    const TYPE_TEXT     = 'TEXT';

    const TYPES = [
        self::TYPE_FIELD    => 'Trường thông tin',
        // self::TYPE_QRCODE   => 'Qrcode',
        self::TYPE_IMG      => 'Ảnh',
        // self::TYPE_TEXT     => 'Text cố định',
    ];

	protected $casts = [
		'card_id'   => 'int',
		// 'pos_x'     => 'int',
		// 'pos_y'     => 'int',
		// 'font_size' => 'int',
		// 'width'     => 'int',
		// 'height'    => 'int'
	];

	protected $fillable = [
		'card_id',
		'card_code',
		'type',
		'field',
		'text',
		'text_wrap',
		'img_path',
		'pos_x',
		'pos_y',
		'font_size',
		'font',
		'width',
		'height',
		'bold',
		'italic',
		'color',
        'v_align',
        'h_align',
        'rotate',
		'status'
	];

	public function card()
	{
		return $this->belongsTo(Card::class);
	}

    /* STATUS */

    static public function getStatues()
    {
        return self::STATUES;
    }

    /* HORIZONTAL ALIGN */

    static public function getHAligns()
    {
        return self::H_ALIGNS;
    }

    public function getHAlignsText()
    {
        return self::H_ALIGNS[$this->h_align];
    }

    /* VERTICAL ALIGN */

    static public function getVAligns()
    {
        return self::V_ALIGNS;
    }

    public function getVAlignsText()
    {
        return self::V_ALIGNS[$this->v_align];
    }

    /* TYPE */

    static public function getTypes()
    {
        return self::TYPES;
    }

    public function getTypesText()
    {
        return self::TYPES[$this->type];
    }

    public function getCssAttributes()
    {
        $bgWidth = null;
        $bgHeight = null;
        if ($this->card->background) {
            try {
                $path = $this->card->backgroundUrl?->getPath();
                if (!empty($path) && is_readable($path)) {
                    $image = Image::make($path);
                    $bgWidth = $image->width();
                    $bgHeight = $image->height();
                }
            } catch (\Throwable $e) {
                $bgWidth = null;
                $bgHeight = null;
            }
        }

        return [
            'font_size' => $this->font_size,
            'font'      => $this->font,
            'color'     => $this->color,
            'h_align'   => $this->h_align,
            'pos_x'     => $this->pos_x,
            'pos_y'     => $this->pos_y,
            'width'     => $this->width,
            'height'    => $this->height,
            'bg_width'  => $bgWidth,
            'bg_height' => $bgHeight,
        ];
    }

    public function generateCssFromAttributes(array $attributes, string $name, ?string $key = null): string
    {
        if (!count($attributes)) return "";
        $css = '<style>';
        $css .= "#{$name} {";

        if ($this->type == self::TYPE_FIELD) {
            // unset($attributes['width']);
            unset($attributes['height']);
            $unit = "%";
            $fontUnit = "px";
        } else {
            unset($attributes['font_size']);
            unset($attributes['font']);
            unset($attributes['color']);
            $unit = "px";
            $fontUnit = "px";
        }

        foreach ($this->getFieldAttributes() as $field => $attr) {
            if (!isset($attributes[$field])) continue;

            switch ($field) {
                case 'bold':
                    if ($attributes[$field] ?? false) {
                        $css .= __("styles.{$field}");
                    }
                    break;
                case 'italic':
                    if ($attributes[$field] ?? false) {
                        $css .= __("styles.{$field}");
                    }
                    break;
                case 'font':
                    $attributes[$field] = $this->getFont($attributes[$field])['name'] ?? null;
                    $css .= __("styles.{$field}", [
                        'value' => $attributes[$field] ?? 'roboto',
                    ]);
                    break;
                case 'font_size':
                    $fontSize = (float)($attributes[$field] ?? 50);
                    $css .= __("styles.{$field}", [
                        'value' => $fontSize,
                        'unit'  => "px",
                    ]);
                    break;
                case 'color':
                    $css .= __("styles.{$field}", [
                        'value' => $attributes[$field] ?? "#000000",
                    ]);
                    break;
                case 'bg_color':
                    $showBg = $attributes['bg'] ?? false;

                    if ($showBg) {
                        $css .= __("styles.{$field}", [
                            'value' => $attributes[$field] ?? "#ffffff",
                        ]);
                        $css .= __("styles.padding", [
                            'top'       => "2",
                            'right'     => "10",
                            'bottom'    => "2",
                            'left'      => "10",
                            'unit'      => "px",
                        ]);
                        $css .= __("styles.border_radius", [
                            'value'     => 10,
                            'unit'      => "px",
                        ]);
                    }
                    break;
                case 'width':
                    $css .= __("styles.{$field}", [
                        'value' => ($attributes[$field] ?? "50").$unit,
                    ]);
                    break;
                case 'height':
                    $css .= __("styles.{$field}", [
                        'value' => ($attributes[$field] ?? "50").$unit,
                    ]);
                    break;
                case 'pos_x':
                    $css .= __("styles.left", [
                        'value'     => ($attributes[$field] ?? 0),
                        'unit'      => "%",
                    ]);
                    break;
                case 'pos_y':
                    $css .= __("styles.top", [
                        'value'     => ($attributes[$field] ?? 0),
                        'unit'      => "%",
                    ]);
                    break;
                case 'h_align':
                    if ($attributes[$field] == self::H_ALIGN_CENTER) {
                        $css .= __("styles.text_align", [
                            'value' => "center",
                        ]);
                        $css .= "transform: translate(-50%, -50%);";
                        break;
                    }

                    $css .= __("styles.text_align", [
                        'value' => $attributes[$field] ?? 'left',
                    ]);
                    break;
                default:
                    break;
            }
        }

        $css .= '}';
        $css .= $this->generateFontFaceCss($this->font);
        $css .= '</style>';
        return $css;
    }

    function generateFontFaceCss(string $font): string
    {
        $font = $this->getFont($font);
        if (!count($font)) return "";
        $fontName = $font['name']; // e.g. "Montserrat-Regular"
        $fontDir = $font['path'];                      // e.g. "montserrat"
        $fontWeight = $font['weight'];                      // e.g. "montserrat"
        $fontStyle = $font['style'];                      // e.g. "montserrat"

        return <<<CSS
        @font-face {
            font-family: '{$fontName}';
            src: url('/assets/fonts/{$fontDir}') format('truetype');
            font-weight: '{$fontWeight}';
            font-style: '{$fontStyle}';
        }
        CSS;
    }

    public static function getFonts()
    {
        return [
            'roboto'            => [
                'name'          => 'roboto',
                'text'          => 'Roboto',
                'weight'        => 'normal',
                'style'         => 'normal',
                'path'          => 'Roboto/Roboto-Regular.ttf',
            ],
            'roboto-bold'       => [
                'name'          => 'roboto',
                'text'          => 'Roboto - In đậm',
                'weight'        => 'bold',
                'style'         => 'normal',
                'path'          => 'Roboto/Roboto-Bold.ttf',
            ],
            'roboto-italic'     => [
                'name'          => 'roboto',
                'text'          => 'Roboto - In nghiêng',
                'weight'        => 'normal',
                'style'         => 'italic',
                'path'          => 'Roboto/Roboto-Italic.ttf',
            ],
            'montserrat'        => [
                'name'          => 'montserrat',
                'text'          => 'Montserrat',
                'weight'        => 'normal',
                'style'         => 'normal',
                'path'          => 'montserrat/Montserrat-Regular.ttf',
            ],
            'montserrat-bold'   => [
                'name'          => 'montserrat',
                'text'          => 'Montserrat - In đậm',
                'weight'        => 'bold',
                'style'         => 'normal',
                'path'          => 'montserrat/Montserrat-Bold.ttf',
            ],
            'montserrat-italic' => [
                'name'          => 'montserrat',
                'text'          => 'Montserrat - In nghiêng',
                'weight'        => 'normal',
                'style'         => 'italic',
                'path'          => 'montserrat/Montserrat-Italic.ttf',
            ],
            'MTD-Portrait-Script-Bounce' => [
                'name'          => 'MTD-Portrait-Script-Bounce',
                'text'          => 'MTD-Portrait-Script-Bounce - In nghiêng',
                'weight'        => 'normal',
                'style'         => 'normal',
                'path'          => 'MTD-Portrait-Script-Bounce.ttf',
            ],
            'HelveticaNeueLTStd-LtCn' => [
                'name'          => 'HelveticaNeueLTStd-LtCn',
                'text'          => 'HelveticaNeueLTStd-LtCn',
                'weight'        => 'normal',
                'style'         => 'normal',
                'path'          => 'HelveticaNeueLTStd-LtCn.ttf',
            ],
            'gilroy-bold'        => [
                'name'          => 'gilroy',
                'text'          => 'gilroy - In đậm',
                'weight'        => '700',
                'style'         => 'normal',
                'path'          => 'gilroy/SVN-Gilroy-Bold.ttf',
            ],
            'gilroy-semibold'   => [
                'name'          => 'gilroy',
                'text'          => 'gilroy - Đậm vừa',
                'weight'        => '600',
                'style'         => 'normal',
                'path'          => 'gilroy/SVN-Gilroy-SemiBold.ttf',
            ],
            'gilroy-xbold'   => [
                'name'          => 'gilroy',
                'text'          => 'gilroy - Rất đậm',
                'weight'        => '800',
                'style'         => 'normal',
                'path'          => 'gilroy/SVN-Gilroy-XBold.ttf',
            ],
            'gilroy-light' => [
                'name'          => 'gilroy',
                'text'          => 'gilroy - Mảnh',
                'weight'        => '300',
                'style'         => 'normal',
                'path'          => 'gilroy/SVN-Gilroy-Light.ttf',
            ],
            'arsenalsc-bold' => [
                'name'          => 'arsenalsc',
                'text'          => 'arsenalsc - Đậm',
                'weight'        => 'bold',
                'style'         => 'normal',
                'path'          => 'arsenalsc/ArsenalSC-Bold.ttf',
            ],
             'UnileverShilling'   => [
                'name'          => 'UnileverShilling',
                'text'          => 'UnileverShilling',
                'weight'        => 'normal',
                'style'         => 'normal',
                'path'          => 'UnileverShilling/UnileverShilling.ttf',
            ],
            'UnileverShillingBold'   => [
                'name'          => 'UnileverShilling',
                'text'          => 'UnileverShillingBold',
                'weight'        => 'bold',
                'style'         => 'normal',
                'path'          => 'UnileverShilling/UnileverShillingBold.ttf',
            ],
            'UnileverShillingItalic' => [
                'name'          => 'UnileverShilling',
                'text'          => 'UnileverShillingItalic',
                'weight'        => 'italic',
                'style'         => 'normal',
                'path'          => 'UnileverShilling/UnileverShillingItalic.ttf',
            ],
            'UnileverShillingMedium' => [
                'name'          => 'UnileverShilling',
                'text'          => 'UnileverShillingMedium',
                'weight'        => 'medium',
                'style'         => 'normal',
                'path'          => 'UnileverShilling/UnileverShillingMedium.ttf',
            ],
            'arsenalsc-bold' => [
                'name'          => 'arsenalsc',
                'text'          => 'arsenalsc - Đậm',
                'weight'        => 'bold',
                'style'         => 'normal',
                'path'          => 'arsenalsc/ArsenalSC-Bold.ttf',
            ],
            // 'svn-gotham-light'  => [
            //     'name'          => 'svn-gotham',
            //     'text'          => 'SVN-Gotham - Light',
            //     'weight'        => 'normal',
            //     'style'         => 'normal',
            //     'path'          => 'SVN-Gotham/SVN-Gotham Light.ttf',
            // ],
        ];
    }

    public function getFont(string $font)
    {
        return $this->getFonts()[$font] ?? [];
    }
}
