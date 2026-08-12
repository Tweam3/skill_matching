<?php

namespace App\Enums;

enum RequestStatus: string
{
    case Open = 'Open';
    case Assigned = 'Assigned';
    case Completed = 'Completed';
    case Cancelled = 'Cancelled';
}
