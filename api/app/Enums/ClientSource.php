<?php

namespace App\Enums;

enum ClientSource: string
{
    case Import = 'import';
    case Landing = 'landing';
    case Manual = 'manual';
    case Api = 'api';
}
