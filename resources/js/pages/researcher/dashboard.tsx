import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

interface ResearcherStats {
    my_papers: number;
    total_views: number;
    followers: number;
    recent_papers: any[];
}

export default function ResearcherDashboard({
    stats,
}: {
    stats: ResearcherStats;
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="mb-4">
                    <h2 className="text-2xl font-bold">Researcher Dashboard</h2>
                    <p className="text-muted-foreground">
                        Manage your research papers and profile
                    </p>
                </div>

                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        <div className="relative z-10 flex h-full flex-col items-center justify-center p-4">
                            <h3 className="text-3xl font-bold">
                                {stats.my_papers}
                            </h3>
                            <p className="text-sm text-muted-foreground">
                                My Papers
                            </p>
                        </div>
                    </div>
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        <div className="relative z-10 flex h-full flex-col items-center justify-center p-4">
                            <h3 className="text-3xl font-bold">
                                {stats.total_views}
                            </h3>
                            <p className="text-sm text-muted-foreground">
                                Total Views
                            </p>
                        </div>
                    </div>
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        <div className="relative z-10 flex h-full flex-col items-center justify-center p-4">
                            <h3 className="text-3xl font-bold">
                                {stats.followers}
                            </h3>
                            <p className="text-sm text-muted-foreground">
                                Followers
                            </p>
                        </div>
                    </div>
                </div>

                <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                    <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    <div className="relative z-10 p-6">
                        <h3 className="mb-4 text-lg font-semibold">
                            Recent Papers
                        </h3>
                        <p className="text-sm text-muted-foreground">
                            Your published research papers will appear here
                        </p>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
