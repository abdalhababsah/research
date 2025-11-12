<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RelatedPaper extends Model
{
    use HasFactory;

    protected $fillable = [
        'research_paper_id',
        'related_research_paper_id',
        'relevance_score',
    ];

    protected function casts(): array
    {
        return [
            'relevance_score' => 'float',
        ];
    }

    // Relationships

    public function researchPaper(): BelongsTo
    {
        return $this->belongsTo(ResearchPaper::class);
    }

    public function relatedResearchPaper(): BelongsTo
    {
        return $this->belongsTo(ResearchPaper::class, 'related_research_paper_id');
    }

    // Scopes

    public function scopeForPaper($query, int $paperId)
    {
        return $query->where('research_paper_id', $paperId);
    }

    public function scopeHighRelevance($query, float $minScore = 0.7)
    {
        return $query->where('relevance_score', '>=', $minScore);
    }

    public function scopeOrderedByRelevance($query)
    {
        return $query->orderBy('relevance_score', 'desc');
    }

    // Helper Methods

    public static function linkPapers(
        ResearchPaper $paper1,
        ResearchPaper $paper2,
        ?float $relevanceScore = null
    ): void {
        // Create bidirectional relationship
        self::updateOrCreate(
            [
                'research_paper_id' => $paper1->id,
                'related_research_paper_id' => $paper2->id,
            ],
            ['relevance_score' => $relevanceScore]
        );

        self::updateOrCreate(
            [
                'research_paper_id' => $paper2->id,
                'related_research_paper_id' => $paper1->id,
            ],
            ['relevance_score' => $relevanceScore]
        );
    }

    public static function unlinkPapers(ResearchPaper $paper1, ResearchPaper $paper2): void
    {
        self::where([
            'research_paper_id' => $paper1->id,
            'related_research_paper_id' => $paper2->id,
        ])->delete();

        self::where([
            'research_paper_id' => $paper2->id,
            'related_research_paper_id' => $paper1->id,
        ])->delete();
    }

    public static function calculateRelevanceScore(
        ResearchPaper $paper1,
        ResearchPaper $paper2
    ): float {
        $score = 0.0;

        // Same category
        if ($paper1->category_id === $paper2->category_id) {
            $score += 0.3;
        }

        // Shared tags
        $sharedTags = $paper1->tags()->pluck('tags.id')
            ->intersect($paper2->tags()->pluck('tags.id'));
        $score += min(0.5, $sharedTags->count() * 0.1);

        // Same author
        if ($paper1->author_id === $paper2->author_id) {
            $score += 0.2;
        }

        return min(1.0, $score);
    }

    public static function generateRelatedPapers(ResearchPaper $paper, int $limit = 10): void
    {
        // Find papers with same category or tags
        $candidates = ResearchPaper::published()
            ->where('id', '!=', $paper->id)
            ->where(function ($query) use ($paper) {
                $query->where('category_id', $paper->category_id)
                    ->orWhereHas('tags', function ($q) use ($paper) {
                        $q->whereIn('tags.id', $paper->tags()->pluck('tags.id'));
                    });
            })
            ->limit($limit * 2) // Get more candidates to calculate scores
            ->get();

        $relatedPapers = $candidates->map(function ($candidate) use ($paper) {
            return [
                'paper' => $candidate,
                'score' => self::calculateRelevanceScore($paper, $candidate),
            ];
        })->sortByDesc('score')
          ->take($limit);

        foreach ($relatedPapers as $item) {
            self::linkPapers($paper, $item['paper'], $item['score']);
        }
    }
}
