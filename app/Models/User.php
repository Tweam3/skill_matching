<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'User_ID';

    public $timestamps = false;

    protected $fillable = [
        'Full_Name',
        'Email',
        'Password_Hash',
        'Role',
        'Council',
        'Student_ID',
        'Profile_Picture',
        'Bio',
        'Created_At',
        'Avg_Rating',
        'Total_Completed',
        'Is_Verified',
        'Account_Status',
        'Warning_Count',
        'Rejection_Reason',
        'settings',
    ];

    protected $hidden = [
        'Password_Hash',
        'remember_token',
    ];

    protected $casts = [
        'Is_Verified' => 'boolean',
        'Created_At' => 'datetime',
        'settings' => 'array',
    ];

    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return $this->Full_Name;
    }

    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['Password_Hash'] = bcrypt($value);
        }
    }

    public function getAuthPassword()
    {
        return $this->Password_Hash;
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'user_skills', 'User_ID', 'Skill_ID')->withPivot('Proficiency');
    }

    public function requests()
    {
        return $this->hasMany(SkillRequest::class, 'User_ID', 'User_ID');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'User_ID', 'User_ID');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'Sender_ID', 'User_ID');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'Receiver_ID', 'User_ID');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'User_ID', 'User_ID');
    }

    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'Reviewed_User_ID', 'User_ID');
    }

    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'Reviewer_ID', 'User_ID');
    }

    public function userMatches()
    {
        return $this->hasMany(UserMatch::class, 'Matched_User_ID', 'User_ID');
    }
}
