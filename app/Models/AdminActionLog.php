<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminActionLog extends Model
{
    protected $table = 'admin_action_logs';

    protected $primaryKey = 'Log_ID';

    public $timestamps = false;

    protected $fillable = ['Admin_ID', 'Action', 'Details', 'Created_At'];

    protected $casts = [
        'Created_At' => 'datetime',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Admin_ID', 'User_ID');
    }
}
