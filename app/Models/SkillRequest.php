<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkillRequest extends Model
{
    protected $table = 'skill_requests';

    protected $primaryKey = 'Request_ID';

    public $timestamps = false;

    protected $fillable = ['User_ID', 'Skill_ID', 'Title', 'Description', 'Status', 'Service_Mode', 'Created_At'];

    protected $casts = [
        'Created_At' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'User_ID', 'User_ID');
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class, 'Skill_ID', 'Skill_ID');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'request_skills', 'Request_ID', 'Skill_ID');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'Request_ID', 'Request_ID');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(UserMatch::class, 'Request_ID', 'Request_ID');
    }
}
