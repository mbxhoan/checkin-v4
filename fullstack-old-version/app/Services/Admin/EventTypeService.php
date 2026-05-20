<?php

namespace App\Services\Admin;

use App\Models\EventType;
use App\Services\BaseService;

class EventTypeService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(EventType::class);
    }
}
