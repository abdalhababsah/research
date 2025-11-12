<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_public',
        'paper_count',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'paper_count' => 'integer',
        ];
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function researchPapers(): BelongsToMany
    {
        return $this->belongsToMany(ResearchPaper::class, 'collection_research_paper')
            ->withPivot('order', 'added_at')
            ->orderBy('order');
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    // Scopes

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper Methods

    public function addPaper(ResearchPaper $paper, ?int $order = null): void
    {
        if (!$this->researchPapers()->where('research_paper_id', $paper->id)->exists()) {
            $maxOrder = $this->researchPapers()->max('order') ?? 0;

            $this->researchPapers()->attach($paper->id, [
                'order' => $order ?? $maxOrder + 1,
                'added_at' => now(),
            ]);

            $this->updatePaperCount();
        }
    }

    public function removePaper(ResearchPaper $paper): void
    {
        $this->researchPapers()->detach($paper->id);
        $this->updatePaperCount();
    }

    public function updatePaperCount(): void
    {
        $this->paper_count = $this->researchPapers()->count();
        $this->save();
    }

    public function reorderPapers(array $paperIds): void
    {
        foreach ($paperIds as $index => $paperId) {
            $this->researchPapers()->updateExistingPivot($paperId, [
                'order' => $index + 1,
            ]);
        }
    }

    public function hasPaper(ResearchPaper $paper): bool
    {
        return $this->researchPapers()->where('research_paper_id', $paper->id)->exists();
    }
}
