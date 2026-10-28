<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillEndorsement extends Model
{
    protected $table = 'skill_endorsements';

    protected $primaryKey = 'Endorsement_ID';

    protected $fillable = ['Endorser_ID', 'User_Skill_ID'];

    public $timestamps = true;

    public function endorser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Endorser_ID', 'User_ID');
    }

    public function userSkill(): BelongsTo
    {
        return $this->belongsTo(UserSkill::class, 'User_Skill_ID', 'User_Skill_ID');
    }
}