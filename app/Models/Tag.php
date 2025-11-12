<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'usage_count',
    ];

    protected function casts(): array
    {
        return [
            'usage_count' => 'integer',
        ];
    }

    // Relationships

    public function researchPapers(): BelongsToMany
    {
        return $this->belongsToMany(ResearchPaper::class, 'research_paper_tag')
            ->withTimestamps();
    }

    public function follows(): HasMany
    {
        return $this->hasMany(Follow::class);
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'tag_id', 'follower_id')
            ->where('following_type', 'tag')
            ->withTimestamps();
    }

    // Mutators

    public function setNameAttribute($value): void
    {
        $normalized = Str::lower(trim($value));
        $this->attributes['name'] = $normalized;
        $this->attributes['slug'] = Str::slug($normalized);
    }

    // Scopes

    public function scopePopular($query, int $limit = 10)
    {
        return $query->orderBy('usage_count', 'desc')->limit($limit);
    }

    public function scopeTrending($query, int $days = 30)
    {
        return $query->withCount(['researchPapers' => function ($query) use ($days) {
            $query->where('created_at', '>=', now()->subDays($days));
        }])->orderBy('research_papers_count', 'desc');
    }

    // Helper Methods

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    public function decrementUsage(): void
    {
        if ($this->usage_count > 0) {
            $this->decrement('usage_count');
        }
    }

    public function syncUsageCount(): void
    {
        $this->usage_count = $this->researchPapers()->count();
        $this->save();
    }
}
