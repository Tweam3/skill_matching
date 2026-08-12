<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $table = 'reviews';

    protected $primaryKey = 'Review_ID';

    public $timestamps = false;

    protected $fillable = ['Reviewed_User_ID', 'Reviewer_ID', 'Request_ID', 'Rating', 'Comment', 'Created_At'];

    protected $casts = [
        'Created_At' => 'datetime',
    ];

    public function reviewedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Reviewed_User_ID', 'User_ID');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Reviewer_ID', 'User_ID');
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(SkillRequest::class, 'Request_ID', 'Request_ID');
    }
}
