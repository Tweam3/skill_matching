<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $table = 'reports';

    protected $primaryKey = 'Report_ID';

    public $timestamps = false;

    protected $fillable = ['Reporter_ID', 'Reported_User_ID', 'Request_ID', 'Reason', 'Proof', 'Status', 'Admin_ID', 'Admin_Note', 'Created_At'];

    protected $casts = [
        'Created_At' => 'datetime',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Reporter_ID', 'User_ID');
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Reported_User_ID', 'User_ID');
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(SkillRequest::class, 'Request_ID', 'Request_ID');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Admin_ID', 'User_ID');
    }
}
