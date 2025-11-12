<?php

namespace App\Repositories;

use App\Models\ResearcherProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ResearcherProfileRepository
{
    /**
     * Find profile by slug.
     */
    public function findBySlug(string $slug): ?ResearcherProfile
    {
        return ResearcherProfile::with(['user'])
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Find profile by user.
     */
    public function findByUser(User $user): ?ResearcherProfile
    {
        return ResearcherProfile::where('user_id', $user->id)->first();
    }

    /**
     * Update profile.
     */
    public function update(ResearcherProfile $profile, array $data): ResearcherProfile
    {
        $profile->update($data);
        return $profile->fresh();
    }

    /**
     * Search profiles.
     */
    public function search(string $query, array $filters = []): Collection
    {
        $queryBuilder = ResearcherProfile::with(['user'])
            ->where(function ($q) use ($query) {
                $q->whereHas('user', function ($userQuery) use ($query) {
                    $userQuery->where('name', 'like', "%{$query}%");
                })
                ->orWhere('institution', 'like', "%{$query}%")
                ->orWhere('department', 'like', "%{$query}%")
                ->orWhereJsonContains('research_interests', $query);
            });

        if (isset($filters['institution'])) {
            $queryBuilder->where('institution', 'like', "%{$filters['institution']}%");
        }

        if (isset($filters['department'])) {
            $queryBuilder->where('department', 'like', "%{$filters['department']}%");
        }

        return $queryBuilder->get();
    }

    /**
     * Get featured profiles.
     */
    public function getFeatured(int $limit = 10): Collection
    {
        return ResearcherProfile::with(['user'])
            ->orderBy('total_views', 'desc')
            ->orderBy('total_downloads', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top profiles by paper count.
     */
    public function getTopByPapers(int $limit = 10): Collection
    {
        return ResearcherProfile::with(['user'])
            ->withCount('user.researchPapers')
            ->orderBy('user_research_papers_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top profiles by followers.
     */
    public function getTopByFollowers(int $limit = 10): Collection
    {
        return ResearcherProfile::with(['user'])
            ->withCount('user.followers')
            ->orderBy('user_followers_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all profiles with pagination.
     */
    public function paginate(int $perPage = 20)
    {
        return ResearcherProfile::with(['user'])
            ->latest()
            ->paginate($perPage);
    }
}
