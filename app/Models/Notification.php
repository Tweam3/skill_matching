<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $primaryKey = 'Notif_ID';

    public $timestamps = false;

    protected $fillable = ['User_ID', 'Notif_Type', 'Message', 'Status', 'Created_At'];

    protected $casts = [
        'Created_At' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'User_ID', 'User_ID');
    }
}
