import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: dashboard().url,
    },
];

interface AdminStats {
    total_users: number;
    total_papers: number;
    pending_reports: number;
    system_health: string;
}

export default function AdminDashboard({ stats }: { stats: AdminStats }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="mb-4">
                    <h2 className="text-2xl font-bold">Admin Dashboard</h2>
                    <p className="text-muted-foreground">
                        Welcome to the admin control panel
                    </p>
                </div>

                <div className="grid auto-rows-min gap-4 md:grid-cols-4">
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        <div className="relative z-10 flex h-full flex-col items-center justify-center p-4">
                            <h3 className="text-3xl font-bold">
                                {stats.total_users}
                            </h3>
                            <p className="text-sm text-muted-foreground">
                                Total Users
                            </p>
                        </div>
                    </div>
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        <div className="relative z-10 flex h-full flex-col items-center justify-center p-4">
                            <h3 className="text-3xl font-bold">
                                {stats.total_papers}
                            </h3>
                            <p className="text-sm text-muted-foreground">
                                Research Papers
                            </p>
                        </div>
                    </div>
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        <div className="relative z-10 flex h-full flex-col items-center justify-center p-4">
                            <h3 className="text-3xl font-bold">
                                {stats.pending_reports}
                            </h3>
                            <p className="text-sm text-muted-foreground">
                                Pending Reports
                            </p>
                        </div>
                    </div>
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                        <div className="relative z-10 flex h-full flex-col items-center justify-center p-4">
                            <h3 className="text-3xl font-bold">
                                {stats.system_health}
                            </h3>
                            <p className="text-sm text-muted-foreground">
                                System Health
                            </p>
                        </div>
                    </div>
                </div>

                <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                    <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    <div className="relative z-10 p-6">
                        <h3 className="mb-4 text-lg font-semibold">
                            Recent Activity
                        </h3>
                        <p className="text-sm text-muted-foreground">
                            Activity feed will appear here
                        </p>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
