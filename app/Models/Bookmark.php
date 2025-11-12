<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bookmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'research_paper_id',
        'collection_id',
    ];

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function researchPaper(): BelongsTo
    {
        return $this->belongsTo(ResearchPaper::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    // Scopes

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeInCollection($query, ?int $collectionId)
    {
        if ($collectionId) {
            return $query->where('collection_id', $collectionId);
        }

        return $query->whereNull('collection_id');
    }

    // Helper Methods

    public function moveToCollection(?Collection $collection): void
    {
        $this->update(['collection_id' => $collection?->id]);
    }
}
