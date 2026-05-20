<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EventType
 * 
 * @property int $id
 * @property string $title
 * @property string $name
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Event[] $events
 *
 * @package App\Models
 */
class EventType extends Model
{
	protected $table = 'event_types';

	protected $fillable = [
		'title',
		'name',
		'description'
	];

	public function events()
	{
		return $this->hasMany(Event::class, 'type_id');
	}
}
