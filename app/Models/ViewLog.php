<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViewLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'research_paper_id',
        'user_id',
        'ip_address',
        'user_agent',
        'referrer',
        'country_code',
        'viewed_at',
        'session_id',
    ];

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
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

    // Scopes

    public function scopeForPaper($query, int $paperId)
    {
        return $query->where('research_paper_id', $paperId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('viewed_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('viewed_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('viewed_at', now()->month)
            ->whereYear('viewed_at', now()->year);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('viewed_at', [$startDate, $endDate]);
    }

    public function scopeByCountry($query, string $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    // Helper Methods

    public static function logView(
        ResearchPaper $paper,
        ?User $user,
        string $ipAddress,
        string $userAgent,
        ?string $referrer,
        ?string $countryCode,
        string $sessionId
    ): self {
        return self::create([
            'research_paper_id' => $paper->id,
            'user_id' => $user?->id,
            'ip_address' => hash('sha256', $ipAddress), // Hash for privacy
            'user_agent' => $userAgent,
            'referrer' => $referrer,
            'country_code' => $countryCode,
            'viewed_at' => now(),
            'session_id' => $sessionId,
        ]);
    }

    public static function getUniqueViewsCount(int $paperId, ?string $period = null): int
    {
        $query = self::where('research_paper_id', $paperId)
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
