<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ResearchPaper extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'abstract',
        'content',
        'author_id',
        'doi',
        'journal_name',
        'publication_date',
        'status',
        'category_id',
        'visibility',
        'scheduled_publication_date',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'publication_date' => 'date',
            'scheduled_publication_date' => 'datetime',
            'is_featured' => 'boolean',
            'view_count' => 'integer',
            'download_count' => 'integer',
        ];
    }

    // Relationships

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'research_paper_tag')
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function coAuthors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'co_authors')
            ->withPivot('status', 'invited_by')
            ->withTimestamps();
    }

    public function acceptedCoAuthors(): BelongsToMany
    {
        return $this->coAuthors()->wherePivot('status', 'accepted');
    }

    public function relatedPapers(): BelongsToMany
    {
        return $this->belongsToMany(
            ResearchPaper::class,
            'related_papers',
            'research_paper_id',
            'related_research_paper_id'
        )->withPivot('relevance_score')->withTimestamps();
    }

    public function references(): HasMany
    {
        return $this->hasMany(Reference::class)->orderBy('reference_order');
    }

    public function viewLogs(): HasMany
    {
        return $this->hasMany(ViewLog::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'activityable');
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    // Mutators

    public function setTitleAttribute($value): void
    {
        $this->attributes['title'] = $value;
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    // Scopes

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    public function scopeVisible($query)
    {
        return $query->whereIn('visibility', ['public', 'registered_only']);
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByAuthor($query, int $userId)
    {
        return $query->where('author_id', $userId);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeWithTag($query, int $tagId)
    {
        return $query->whereHas('tags', function ($q) use ($tagId) {
            $q->where('tags.id', $tagId);
        });
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors

    public function getAuthorsListAttribute(): string
    {
        $authors = collect([$this->author]);
        $coAuthors = $this->acceptedCoAuthors;

        return $authors->merge($coAuthors)->pluck('name')->join(', ');
    }

    public function getReadingTimeAttribute(): int
    {
        $words = str_word_count(strip_tags($this->content ?? ''));
        return (int) ceil($words / 200); // Average reading speed: 200 words/minute
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'published' => ['text' => 'Published', 'class' => 'success'],
            'draft' => ['text' => 'Draft', 'class' => 'secondary'],
            'under_review' => ['text' => 'Under Review', 'class' => 'warning'],
            default => ['text' => 'Unknown', 'class' => 'default'],
        };
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->publication_date?->format('F j, Y') ?? 'Not published';
    }

    // Helper Methods

    public function isVisibleTo(?User $user): bool
    {
        return match ($this->visibility) {
            'public' => true,
            'registered_only' => $user !== null,
            'unlisted' => false,
            'private' => $user && ($user->id === $this->author_id || $user->isAdmin()),
            default => false,
        };
    }

    public function canEdit(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->id === $this->author_id || $user->isAdmin();
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    public function incrementDownloads(): void
    {
        $this->increment('download_count');
    }

    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'publication_date' => now(),
        ]);
    }

    public function getAllAuthors(): \Illuminate\Support\Collection
    {
        return collect([$this->author])->merge($this->acceptedCoAuthors);
    }
}
