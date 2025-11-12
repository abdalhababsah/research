<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Follow extends Model
{
    use HasFactory;

    protected $fillable = [
        'follower_id',
        'following_type',
        'following_id',
        'tag_id',
    ];

    protected function casts(): array
    {
        return [
            'following_type' => 'string',
        ];
    }

    // Relationships

    public function follower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function following(): BelongsTo
    {
        return $this->belongsTo(User::class, 'following_id');
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    // Scopes

    public function scopeResearchers($query)
    {
        return $query->where('following_type', 'researcher');
    }

    public function scopeTags($query)
    {
        return $query->where('following_type', 'tag');
    }

    // Helper Methods

    public function isResearcherFollow(): bool
    {
        return $this->following_type === 'researcher';
    }

    public function isTagFollow(): bool
    {
        return $this->following_type === 'tag';
    }
}
