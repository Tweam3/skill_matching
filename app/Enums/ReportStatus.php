<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Pending = 'Pending';
    case Dismissed = 'Dismissed';
    case ActionTaken = 'Action_Taken';
}
