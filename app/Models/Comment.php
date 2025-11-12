<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'research_paper_id',
        'user_id',
        'parent_comment_id',
        'content',
        'upvotes',
        'downvotes',
        'is_flagged',
    ];

    protected function casts(): array
    {
        return [
            'upvotes' => 'integer',
            'downvotes' => 'integer',
            'is_flagged' => 'boolean',
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

    public function parentComment(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_comment_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_comment_id')->orderBy('created_at');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(CommentVote::class);
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    // Scopes

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_comment_id');
    }

    public function scopeOrderByScore($query)
    {
        return $query->orderByRaw('(upvotes - downvotes) DESC');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Accessors

    public function getScoreAttribute(): int
    {
        return $this->upvotes - $this->downvotes;
    }

    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parentComment;

        while ($parent) {
            $depth++;
            $parent = $parent->parentComment;
        }

        return $depth;
    }

    // Helper Methods

    public function hasReplies(): bool
    {
        return $this->replies()->exists();
    }

    public function isEdited(): bool
    {
        return $this->created_at->ne($this->updated_at);
    }

    public function upvote(User $user): void
    {
        $existingVote = $this->votes()->where('user_id', $user->id)->first();

        if ($existingVote) {
            if ($existingVote->vote_type === 'downvote') {
                $existingVote->update(['vote_type' => 'upvote']);
                $this->decrement('downvotes');
                $this->increment('upvotes');
            }
        } else {
            $this->votes()->create([
                'user_id' => $user->id,
                'vote_type' => 'upvote',
            ]);
            $this->increment('upvotes');
        }
    }

    public function downvote(User $user): void
    {
        $existingVote = $this->votes()->where('user_id', $user->id)->first();

        if ($existingVote) {
            if ($existingVote->vote_type === 'upvote') {
                $existingVote->update(['vote_type' => 'downvote']);
                $this->decrement('upvotes');
                $this->increment('downvotes');
            }
        } else {
            $this->votes()->create([
                'user_id' => $user->id,
                'vote_type' => 'downvote',
            ]);
            $this->increment('downvotes');
        }
    }

    public function removeVote(User $user): void
    {
        $vote = $this->votes()->where('user_id', $user->id)->first();

        if ($vote) {
            if ($vote->vote_type === 'upvote') {
                $this->decrement('upvotes');
            } else {
                $this->decrement('downvotes');
            }
            $vote->delete();
        }
    }

    public function flag(): void
    {
        $this->update(['is_flagged' => true]);
    }

    public function unflag(): void
    {
        $this->update(['is_flagged' => false]);
    }
}
