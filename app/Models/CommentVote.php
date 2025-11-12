<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'comment_id',
        'user_id',
        'vote_type',
    ];

    protected function casts(): array
    {
        return [
            'vote_type' => 'string',
        ];
    }

    // Relationships

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper Methods

    public function isUpvote(): bool
    {
        return $this->vote_type === 'upvote';
    }

    public function isDownvote(): bool
    {
        return $this->vote_type === 'downvote';
    }
}
