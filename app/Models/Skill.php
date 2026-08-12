<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skill extends Model
{
    protected $table = 'skills';

    protected $primaryKey = 'Skill_ID';

    public $timestamps = false;

    protected $fillable = ['Skill_Title', 'Category', 'Subcategory'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_skills', 'Skill_ID', 'User_ID');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(SkillRequest::class, 'Skill_ID', 'Skill_ID');
    }
}
