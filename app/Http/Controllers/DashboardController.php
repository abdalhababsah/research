<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard based on user role.
     */
    public function index(): Response
    {
        $user = auth()->user();

        // Admin dashboard with admin-specific data
        if ($user->isAdmin()) {
            return Inertia::render('admin/dashboard', [
                'stats' => $this->getAdminStats(),
            ]);
        }

        // Researcher dashboard with researcher-specific data
        return Inertia::render('researcher/dashboard', [
            'stats' => $this->getResearcherStats($user),
        ]);
    }

    /**
     * Get statistics for admin dashboard.
     */
    private function getAdminStats(): array
    {
        return [
            'total_users' => \App\Models\User::count(),
            'total_papers' => \App\Models\ResearchPaper::count(),
            'pending_reports' => \App\Models\Report::pending()->count(),
            'system_health' => 'Good', // Can be expanded with actual health checks
        ];
    }

    /**
     * Get statistics for researcher dashboard.
     */
    private function getResearcherStats($user): array
    {
        return [
            'my_papers' => $user->researchPapers()->count(),
            'total_views' => $user->getTotalViews(),
            'followers' => $user->followers()->count(),
            'recent_papers' => $user->researchPapers()
                ->with('category', 'tags')
                ->latest()
                ->take(5)
                ->get(),
        ];
    }
}
