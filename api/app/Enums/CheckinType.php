<?php

namespace App\Enums;

enum CheckinType: string
{
    case CheckIn = 'check_in';
    case CheckOut = 'check_out';
}
