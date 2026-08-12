<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $table = 'messages';

    protected $primaryKey = 'Message_ID';

    public $timestamps = false;

    protected $fillable = ['Sender_ID', 'Receiver_ID', 'Message_Text', 'Sent_At'];

    protected $casts = [
        'Sent_At' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Sender_ID', 'User_ID');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Receiver_ID', 'User_ID');
    }
}
