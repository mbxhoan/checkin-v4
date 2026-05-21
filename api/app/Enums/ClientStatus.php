<?php

namespace App\Enums;

enum ClientStatus: string
{
    case Registered = 'registered';
    case CheckedIn = 'checked_in';
    case CheckedOut = 'checked_out';
    case Cancelled = 'cancelled';
}
