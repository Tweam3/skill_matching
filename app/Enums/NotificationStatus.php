<?php

namespace App\Enums;

enum NotificationStatus: string
{
    case Unread = 'Unread';
    case Read = 'Read';
}
