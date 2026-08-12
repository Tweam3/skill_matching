<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSkill extends Model
{
    protected $table = 'user_skills';

    protected $primaryKey = 'User_Skill_ID';

    public $timestamps = false;

    protected $fillable = ['User_ID', 'Skill_ID', 'Proficiency'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'User_ID', 'User_ID');
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class, 'Skill_ID', 'Skill_ID');
    }
}
