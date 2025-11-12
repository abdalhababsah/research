<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'activity_feeds';

    protected $fillable = [
        'user_id',
        'activity_type',
        'activityable_id',
        'activityable_type',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activityable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes

    public function scopeRecent($query, int $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('activity_type', $type);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeFollowedUsers($query, User $user)
    {
        $followingIds = $user->following()->pluck('users.id');

        return $query->whereIn('user_id', $followingIds);
    }

    // Helper Methods

    public static function log(User $user, string $type, Model $subject, array $metadata = []): self
    {
        return self::create([
            'user_id' => $user->id,
            'activity_type' => $type,
            'activityable_id' => $subject->id,
            'activityable_type' => get_class($subject),
            'metadata' => $metadata,
        ]);
    }

    public function getDescription(): string
    {
        return match ($this->activity_type) {
            'published_paper' => 'published a new research paper',
            'commented' => 'commented on a research paper',
            'followed_user' => 'followed a researcher',
            'followed_tag' => 'followed a tag',
            'bookmarked' => 'bookmarked a research paper',
            'uploaded_file' => 'uploaded a new file',
            default => 'performed an action',
        };
    }
}
