<?php

namespace App\Services;

use App\Models\ResearcherProfile;
use App\Models\User;
use App\Repositories\ResearcherProfileRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ResearcherProfileService
{
    public function __construct(
        private ResearcherProfileRepository $repository
    ) {}

    /**
     * Create a researcher profile for a user.
     */
    public function createProfile(User $user): ResearcherProfile
    {
        $slug = $this->generateSlug($user->name);

        return $user->researcherProfile()->create([
            'slug' => $slug,
        ]);
    }

    /**
     * Update researcher profile.
     */
    public function updateProfile(ResearcherProfile $profile, array $data): ResearcherProfile
    {
        // Ensure slug is unique if being changed
        if (isset($data['slug']) && $data['slug'] !== $profile->slug) {
            $data['slug'] = $this->ensureUniqueSlug($data['slug'], $profile->id);
        }

        return $this->repository->update($profile, $data);
    }

    /**
     * Upload and process profile photo.
     */
    public function uploadPhoto(ResearcherProfile $profile, UploadedFile $file): string
    {
        // Delete old photo if exists
        if ($profile->profile_photo_path) {
            Storage::disk('public')->delete($profile->profile_photo_path);
        }

        // Generate unique filename
        $filename = 'profiles/' . Str::uuid() . '.jpg';

        // Resize and optimize image
        $image = Image::make($file)
            ->fit(400, 400)
            ->encode('jpg', 85);

        // Store image
        Storage::disk('public')->put($filename, $image);

        // Update profile
        $profile->update(['profile_photo_path' => $filename]);

        return $filename;
    }

    /**
     * Remove profile photo.
     */
    public function removePhoto(ResearcherProfile $profile): void
    {
        if ($profile->profile_photo_path) {
            Storage::disk('public')->delete($profile->profile_photo_path);
            $profile->update(['profile_photo_path' => null]);
        }
    }

    /**
     * Generate a unique slug from name.
     */
    public function generateSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        return $this->ensureUniqueSlug($baseSlug);
    }

    /**
     * Ensure slug is unique by appending number if necessary.
     */
    private function ensureUniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $query = ResearcherProfile::where('slug', $slug);

            if ($ignoreId) {
                $query->where('user_id', '!=', $ignoreId);
            }

            if (!$query->exists()) {
                break;
            }

            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Calculate profile statistics.
     */
    public function calculateStats(User $user): array
    {
        return [
            'total_papers' => $user->researchPapers()->count(),
            'published_papers' => $user->researchPapers()->published()->count(),
            'total_views' => $user->getTotalViews(),
            'total_downloads' => $user->getTotalDownloads(),
            'followers' => $user->followers()->count(),
            'following' => $user->following()->count(),
        ];
    }

    /**
     * Calculate profile completion percentage.
     */
    public function getCompletionPercentage(ResearcherProfile $profile): int
    {
        $fields = [
            'title' => 5,
            'institution' => 10,
            'department' => 10,
            'bio' => 15,
            'profile_photo_path' => 15,
            'research_interests' => 15,
            'education_history' => 15,
            'work_experience' => 15,
        ];

        $completed = 0;

        foreach ($fields as $field => $weight) {
            $value = $profile->{$field};

            if ($field === 'research_interests' || $field === 'education_history' || $field === 'work_experience') {
                if (is_array($value) && count($value) > 0) {
                    $completed += $weight;
                }
            } else {
                if (!empty($value)) {
                    $completed += $weight;
                }
            }
        }

        return $completed;
    }

    /**
     * Increment profile views.
     */
    public function incrementViews(ResearcherProfile $profile): void
    {
        $profile->incrementViews();
    }
}
