<?php

namespace App\Enums;

enum EventUserRole: string
{
    case Manager = 'manager';
    case Staff = 'staff';
    case Scanner = 'scanner';
}
