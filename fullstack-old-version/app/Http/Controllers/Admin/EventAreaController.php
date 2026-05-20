<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\EventAreaService;

class EventAreaController extends Controller
{
    public function __construct(EventAreaService $service)
    {
        $this->service = $service;
    }
}
