<?php

namespace App\Services\Web;

use App\Models\Card;
use App\Services\BaseService;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CardService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Card::class);
    }
}
