<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EmailLog
 * 
 * @property int $id
 * @property int $event_id
 * @property string|null $name
 * @property string|null $email
 * @property string|null $content
 * @property Carbon|null $sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Event $event
 *
 * @package App\Models
 */
class EmailLog extends BaseModel
{
	protected $table = 'email_logs';

	protected $casts = [
		'event_id' => 'int'
	];

	protected $dates = [
		'sent_at'
	];

	protected $fillable = [
		'event_id',
		'name',
		'email',
		'content',
		'sent_at'
	];

	public function event()
	{
		return $this->belongsTo(Event::class);
	}
}
