<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoAuthor extends Model
{
    use HasFactory;

    protected $fillable = [
        'research_paper_id',
        'user_id',
        'status',
        'invited_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    // Relationships

    public function researchPaper(): BelongsTo
    {
        return $this->belongsTo(ResearchPaper::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    // Scopes

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeDeclined($query)
    {
        return $query->where('status', 'declined');
    }

    // Helper Methods

    public function accept(): void
    {
        $this->update(['status' => 'accepted']);
    }

    public function decline(): void
    {
        $this->update(['status' => 'declined']);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }
}
