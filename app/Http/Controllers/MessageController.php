<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $uid = Auth::id();
        $withId = $request->query('with');
        $messages = [];
        if ($withId) {
            $query = Message::where(function ($q) use ($uid, $withId) {
                $q->where('Sender_ID', $uid)->where('Receiver_ID', $withId);
                $q->orWhere('Sender_ID', $withId)->where('Receiver_ID', $uid);
            })->orderBy('Sent_At')->take(200);

            if ($request->query('since')) {
                $query->where('Message_ID', '>', (int) $request->query('since'));
            }

            $messages = $query->get();
        }
        $partners = Message::where('Sender_ID', $uid)
            ->orWhere('Receiver_ID', $uid)
            ->get()
            ->map(function ($m) use ($uid) {
                return $m->Sender_ID == $uid ? $m->Receiver_ID : $m->Sender_ID;
            })
            ->unique()
            ->toArray();
        $users = User::whereIn('User_ID', $partners)->get(['User_ID', 'Full_Name']);

        if ($request->query('ajax')) {
            return response()->json([
                'messages' => $messages,
                'last_id' => $messages->last()?->Message_ID ?? (int) $request->query('since', 0),
            ]);
        }

        return view('messages.index', compact('messages', 'withId', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'to_id' => ['required', 'integer', 'exists:users,User_ID'],
            'message' => ['required', 'string'],
        ]);
        $message = Message::create([
            'Sender_ID' => Auth::id(),
            'Receiver_ID' => $validated['to_id'],
            'Message_Text' => $validated['message'],
            'Sent_At' => now(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => $message]);
        }

        return redirect()->route('messages.index', ['with' => $validated['to_id']]);
    }
}
