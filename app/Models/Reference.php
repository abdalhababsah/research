<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reference extends Model
{
    use HasFactory;

    protected $fillable = [
        'research_paper_id',
        'reference_text',
        'reference_order',
    ];

    protected function casts(): array
    {
        return [
            'reference_order' => 'integer',
        ];
    }

    // Relationships

    public function researchPaper(): BelongsTo
    {
        return $this->belongsTo(ResearchPaper::class);
    }

    // Scopes

    public function scopeOrdered($query)
    {
        return $query->orderBy('reference_order');
    }

    public function scopeForPaper($query, int $paperId)
    {
        return $query->where('research_paper_id', $paperId);
    }

    // Helper Methods

    public static function createForPaper(ResearchPaper $paper, array $references): void
    {
        foreach ($references as $index => $referenceText) {
            self::create([
                'research_paper_id' => $paper->id,
                'reference_text' => $referenceText,
                'reference_order' => $index + 1,
            ]);
        }
    }

    public static function updateForPaper(ResearchPaper $paper, array $references): void
    {
        // Delete existing references
        self::where('research_paper_id', $paper->id)->delete();

        // Create new references
        self::createForPaper($paper, $references);
    }

    public function moveUp(): bool
    {
        if ($this->reference_order <= 1) {
            return false;
        }

        $previousReference = self::where('research_paper_id', $this->research_paper_id)
            ->where('reference_order', $this->reference_order - 1)
            ->first();

        if ($previousReference) {
            $previousReference->update(['reference_order' => $this->reference_order]);
            $this->update(['reference_order' => $this->reference_order - 1]);
            return true;
        }

        return false;
    }

    public function moveDown(): bool
    {
        $nextReference = self::where('research_paper_id', $this->research_paper_id)
            ->where('reference_order', $this->reference_order + 1)
            ->first();

        if ($nextReference) {
            $nextReference->update(['reference_order' => $this->reference_order]);
            $this->update(['reference_order' => $this->reference_order + 1]);
            return true;
        }

        return false;
    }
}
