<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Email
 *
 * @property int $id
 * @property int $campaign_id
 * @property string|null $subject
 * @property string|null $email
 * @property string|null $content
 * @property Carbon|null $sent_at
 * @property string|null $from_name
 * @property string|null $from_email
 * @property string|null $to_name
 * @property string|null $to_email
 * @property string|null $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Campaign $campaign
 *
 * @package App\Models
 */
class Email extends BaseModel
{
	protected $table = 'emails';

	const STATUS_NEW 		= 'NEW';
	const STATUS_WAITING 	= 'WAITING';
	const STATUS_SENT 		= 'SENT';
	const STATUS_CLOSED 	= 'CLOSED';

    const STATUES = [
        self::STATUS_NEW 		=> 'Dừng',
        self::STATUS_WAITING 	=> 'Hàng đợi',
        self::STATUS_SENT 		=> 'Đã gửi',
        self::STATUS_CLOSED 	=> 'Đã đóng',
    ];

    const STATUS_CLASS = [
        self::STATUS_NEW 		=> 'btn-danger',
        self::STATUS_WAITING 	=> 'btn-secondary',
        self::STATUS_SENT 		=> 'btn-success',
        self::STATUS_CLOSED 	=> 'btn-danger',
    ];

	protected $casts = [
		'campaign_id' => 'int'
	];

	protected $dates = [
		'sent_at'
	];

	protected $fillable = [
		'campaign_id',
        'message_id',
		'subject',
		'email',
		'qrcode',
		'content',
		'template_id',
		'is_online',
		'supplier',
		'param',
		'sent_at',
        'server_response',
		'from_name',
		'from_email',
		'to_name',
		'to_email',
		'status',
        'error_log'
	];

    /**
     * The `emails.error_log` column is JSON. Always persist a valid JSON document.
     *
     * - Arrays/objects are encoded directly.
     * - Plain strings are wrapped into {"message": "..."} to avoid MySQL JSON errors.
     * - Already-valid JSON strings are stored as-is.
     */
    public function setErrorLogAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['error_log'] = null;
            return;
        }

        if (is_array($value) || is_object($value)) {
            $this->attributes['error_log'] = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            return;
        }

        if (is_string($value)) {
            json_decode($value);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->attributes['error_log'] = $value;
                return;
            }

            $this->attributes['error_log'] = json_encode([
                'message' => $value,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            return;
        }

        $this->attributes['error_log'] = json_encode([
            'message' => (string) $value,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

	public function campaign()
	{
		return $this->belongsTo(Campaign::class);
	}

	public function client()
    {
        return $this->belongsTo(Client::class, 'qrcode', 'qrcode');
    }

    public function webhookPostmarks()
    {
        return $this->hasMany(WebhookPostmark::class, 'message_id', 'message_id');
    }

	static public function getStatues()
    {
        return self::STATUES;
    }

    public function getStatusText()
    {
        return self::STATUES[$this->status];
    }

    public function getStatusClass()
    {
        return self::STATUS_CLASS[$this->status];
    }

    public static function checkEmailForm($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }
    }
}
