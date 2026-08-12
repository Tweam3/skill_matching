<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMatch extends Model
{
    protected $table = 'matches';

    protected $primaryKey = 'Match_ID';

    public $timestamps = false;

    protected $fillable = ['Matched_User_ID', 'Request_ID', 'Match_Score', 'Matched_At'];

    protected $casts = [
        'Matched_At' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Matched_User_ID', 'User_ID');
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(SkillRequest::class, 'Request_ID', 'Request_ID');
    }
}
