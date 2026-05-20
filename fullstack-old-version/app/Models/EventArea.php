<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EventArea
 * 
 * @property int $id
 * @property int $event_id
 * @property string $name
 * @property string|null $description
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class EventArea extends Model
{
	protected $table = 'event_areas';

	protected $casts = [
		'event_id' => 'int'
	];

	protected $fillable = [
		'event_id',
		'name',
		'description',
		'note'
	];
}
