<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserSkill extends Model
{
    protected $table = 'user_skills';

    protected $primaryKey = 'User_Skill_ID';

    public $timestamps = false;

    protected $fillable = ['User_ID', 'Skill_ID', 'Proficiency', 'Status'];

    protected $casts = [
        'Proficiency' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'User_ID', 'User_ID');
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class, 'Skill_ID', 'Skill_ID');
    }

    public function endorsements(): HasMany
    {
        return $this->hasMany(SkillEndorsement::class, 'User_Skill_ID', 'User_Skill_ID');
    }

    public function endorsementCount(): int
    {
        return $this->endorsements()->count();
    }
}
