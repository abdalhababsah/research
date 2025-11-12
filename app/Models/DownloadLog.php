<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DownloadLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'file_id',
        'user_id',
        'ip_address',
        'downloaded_at',
        'session_id',
    ];

    protected function casts(): array
    {
        return [
            'downloaded_at' => 'datetime',
        ];
    }

    // Relationships

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes

    public function scopeForFile($query, int $fileId)
    {
        return $query->where('file_id', $fileId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('downloaded_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('downloaded_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('downloaded_at', now()->month)
            ->whereYear('downloaded_at', now()->year);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('downloaded_at', [$startDate, $endDate]);
    }

    // Helper Methods

    public static function logDownload(
        File $file,
        ?User $user,
        string $ipAddress,
        string $sessionId
    ): self {
        return self::create([
            'file_id' => $file->id,
            'user_id' => $user?->id,
            'ip_address' => hash('sha256', $ipAddress), // Hash for privacy
            'downloaded_at' => now(),
            'session_id' => $sessionId,
        ]);
    }

    public static function getUniqueDownloadsCount(int $fileId, ?string $period = null): int
    {
        $query = self::where('file_id', $fileId)
            ->distinct('session_id');

        if ($period) {
            $query = match ($period) {
                'today' => $query->today(),
                'week' => $query->thisWeek(),
                'month' => $query->thisMonth(),
                default => $query,
            };
        }

        return $query->count();
    }
}
