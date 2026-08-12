<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $uid = Auth::id();
        $notifications = Notification::where('User_ID', $uid)->latest('Created_At')->get();
        foreach ($notifications as $n) {
            if ($n->Status === 'Unread') {
                $n->update(['Status' => 'Read']);
            }
        }

        return view('notifications.index', compact('notifications'));
    }
}
