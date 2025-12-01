<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'role_title',
        'years_exp',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function forms(): HasMany
    {
        return $this->hasMany(Form::class);
    }

    public function savedByRecruiters(): HasMany
    {
        return $this->hasMany(SavedCandidate::class);
    }

    public function latestAptitudeAssessment()
    {
        return $this->hasOneThrough(
            Assessment::class,
            Form::class,
            'candidate_id', // Foreign key on forms table
            'form_id',      // Foreign key on assessments table
            'id',           // Local key on candidates table
            'id'            // Local key on forms table
        )->where('forms.form_type', 'aptitude')
          ->where('forms.status', 'submitted')
          ->latest('assessments.created_at');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}

