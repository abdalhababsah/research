<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    // Relationships

    public function researcherProfile(): HasOne
    {
        return $this->hasOne(ResearcherProfile::class);
    }

    public function researchPapers(): HasMany
    {
        return $this->hasMany(ResearchPaper::class, 'author_id');
    }

    public function coAuthoredPapers(): BelongsToMany
    {
        return $this->belongsToMany(ResearchPaper::class, 'co_authors')
            ->withPivot('status', 'invited_by')
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')
            ->where('following_type', 'researcher')
            ->withTimestamps();
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')
            ->where('following_type', 'researcher')
            ->withTimestamps();
    }

    public function followedTags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'follows', 'follower_id', 'tag_id')
            ->where('following_type', 'tag')
            ->withTimestamps();
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function commentVotes(): HasMany
    {
        return $this->hasMany(CommentVote::class);
    }

    // Helper Methods

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isResearcher(): bool
    {
        return $this->role === 'researcher';
    }

    public function isPublic(): bool
    {
        return $this->role === 'public' || $this->role === null;
    }

    public function getTotalViews(): int
    {
        return $this->researcherProfile?->total_views ?? 0;
    }

    public function getTotalDownloads(): int
    {
        return $this->researcherProfile?->total_downloads ?? 0;
    }

    public function hasBookmarked(ResearchPaper $paper): bool
    {
        return $this->bookmarks()->where('research_paper_id', $paper->id)->exists();
    }

    public function isFollowing(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    public function isFollowingTag(Tag $tag): bool
    {
        return $this->followedTags()->where('tag_id', $tag->id)->exists();
    }
}
