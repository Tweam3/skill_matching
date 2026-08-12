<?php

namespace App\Enums;

enum AssignmentStatus: string
{
    case Pending = 'Pending';
    case Accepted = 'Accepted';
    case Rejected = 'Rejected';
    case Active = 'Active';
    case Completed = 'Completed';
    case Failed = 'Failed';
}
