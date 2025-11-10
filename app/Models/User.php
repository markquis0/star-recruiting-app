<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function candidate()
    {
        return $this->hasOne(Candidate::class);
    }

    public function recruiter()
    {
        return $this->hasOne(Recruiter::class);
    }

    public function isCandidate(): bool
    {
        return $this->role === 'candidate';
    }

    public function isRecruiter(): bool
    {
        return $this->role === 'recruiter';
    }
}

