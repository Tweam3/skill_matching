<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assignment extends Model
{
    protected $table = 'request_assignments';

    protected $primaryKey = 'Assignment_ID';

    public $timestamps = false;

    protected $fillable = ['Request_ID', 'User_ID', 'Status', 'Created_At', 'Responded_At', 'Status_Updated_At', 'Completed_At'];

    protected $casts = [
        'Created_At' => 'datetime',
        'Responded_At' => 'datetime',
        'Status_Updated_At' => 'datetime',
        'Completed_At' => 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(SkillRequest::class, 'Request_ID', 'Request_ID');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'User_ID', 'User_ID');
    }
}
