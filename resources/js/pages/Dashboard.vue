<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { Users, UserCog, LayoutGrid } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const page = usePage();
const user = page.props.auth.user;

interface Statistics {
    clients?: {
        active: number;
        total: number;
    };
    managers?: {
        active: number;
        total: number;
    };
}

const statistics = page.props.statistics as Statistics | undefined;
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
        >
            <!-- Welcome Section -->
            <div class="space-y-2">
                <h1 class="text-3xl font-bold tracking-tight">
                    Welcome back, {{ user.name }}!
                </h1>
                <p class="text-muted-foreground">
                    Manage your clients and team members from this dashboard.
                </p>
            </div>

            <!-- Statistics Section -->
            <div
                v-if="statistics"
                class="grid gap-4 md:grid-cols-2 lg:grid-cols-3"
            >
                <!-- Clients Statistics -->
                <div
                    class="rounded-lg border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">
                                Клиенты
                            </p>
                            <p class="mt-2 text-3xl font-bold">
                                {{ statistics.clients?.active || 0 }}/{{
                                    statistics.clients?.total || 0
                                }}
                            </p>
                        </div>
                        <div
                            class="flex size-12 items-center justify-center rounded-lg bg-primary/10 text-primary"
                        >
                            <Users class="size-6" />
                        </div>
                    </div>
                </div>

                <!-- Managers Statistics -->
                <div
                    v-if="statistics.managers"
                    class="rounded-lg border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">
                                Менеджеры
                            </p>
                            <p class="mt-2 text-3xl font-bold">
                                {{ statistics.managers.active }}/{{
                                    statistics.managers.total
                                }}
                            </p>
                        </div>
                        <div
                            class="flex size-12 items-center justify-center rounded-lg bg-primary/10 text-primary"
                        >
                            <UserCog class="size-6" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div
                    class="group relative overflow-hidden rounded-lg border border-sidebar-border/70 bg-card p-6 transition-all hover:shadow-md dark:border-sidebar-border"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex size-12 items-center justify-center rounded-lg bg-primary/10 text-primary"
                        >
                            <Users class="size-6" />
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold">Clients</h3>
                            <p class="text-sm text-muted-foreground">
                                Manage your clients
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-if="user.role === 'super_manager' || user.role === 'admin'"
                    class="group relative overflow-hidden rounded-lg border border-sidebar-border/70 bg-card p-6 transition-all hover:shadow-md dark:border-sidebar-border"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex size-12 items-center justify-center rounded-lg bg-primary/10 text-primary"
                        >
                            <UserCog class="size-6" />
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold">Managers</h3>
                            <p class="text-sm text-muted-foreground">
                                Manage team members
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="group relative overflow-hidden rounded-lg border border-sidebar-border/70 bg-card p-6 transition-all hover:shadow-md dark:border-sidebar-border"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex size-12 items-center justify-center rounded-lg bg-primary/10 text-primary"
                        >
                            <LayoutGrid class="size-6" />
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold">Overview</h3>
                            <p class="text-sm text-muted-foreground">
                                System overview
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Information Section -->
            <div
                class="rounded-lg border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border"
            >
                <h2 class="mb-4 text-xl font-semibold">About AI CMS</h2>
                <div class="space-y-2 text-sm text-muted-foreground">
                    <p>
                        AI CMS is a client management system designed to help you
                        efficiently manage your clients and team members.
                    </p>
                    <p>
                        Use the navigation menu to access different sections of the
                        application.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
