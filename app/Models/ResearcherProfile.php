<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ResearcherProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'institution',
        'department',
        'profile_photo_path',
        'bio',
        'research_interests',
        'contact_information',
        'slug',
        'education_history',
        'work_experience',
        'total_views',
        'total_downloads',
    ];

    protected function casts(): array
    {
        return [
            'research_interests' => 'array',
            'contact_information' => 'array',
            'education_history' => 'array',
            'work_experience' => 'array',
            'total_views' => 'integer',
            'total_downloads' => 'integer',
        ];
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessors

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (!$this->profile_photo_path) {
            return null;
        }

        if (Str::startsWith($this->profile_photo_path, ['http://', 'https://'])) {
            return $this->profile_photo_path;
        }

        return asset('storage/' . $this->profile_photo_path);
    }

    public function getFullNameAttribute(): string
    {
        $title = $this->title ? $this->title . ' ' : '';
        return $title . $this->user->name;
    }

    // Mutators

    public function setSlugAttribute($value): void
    {
        if (empty($value) && $this->user) {
            $this->attributes['slug'] = Str::slug($this->user->name);
        } else {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    // Scopes

    public function scopeWhereInstitution($query, string $institution)
    {
        return $query->where('institution', 'like', "%{$institution}%");
    }

    public function scopeWhereHasInterest($query, string $interest)
    {
        return $query->whereJsonContains('research_interests', $interest);
    }

    // Helper Methods

    public function incrementViews(): void
    {
        $this->increment('total_views');
    }

    public function incrementDownloads(): void
    {
        $this->increment('total_downloads');
    }
}
