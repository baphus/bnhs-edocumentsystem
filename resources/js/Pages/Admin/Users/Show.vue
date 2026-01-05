<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { User } from '@/types';

const props = defineProps<{
    user: User & { logs?: any[] };
}>();

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
    });
};

const getRoleColor = (role: string) => {
    const colors: Record<string, string> = {
        'admin': 'bg-purple-100 text-purple-800',
        'registrar': 'bg-blue-100 text-blue-800',
        'guest': 'bg-gray-100 text-gray-800',
    };
    return colors[role] || colors['guest'];
};
</script>

<template>
    <Head :title="`User Details - ${user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('admin.users.index')" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    User Details
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-6 md:grid-cols-3">
                    <!-- User Info -->
                    <div class="md:col-span-1">
                        <div class="overflow-hidden rounded-xl bg-white shadow">
                            <div class="p-6">
                                <div class="flex flex-col items-center text-center">
                                    <div class="flex h-24 w-24 items-center justify-center rounded-full bg-bnhs-blue text-3xl font-bold text-white">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <h3 class="mt-4 text-lg font-medium text-gray-900">{{ user.name }}</h3>
                                    <p class="text-sm text-gray-500">{{ user.email }}</p>
                                    <div class="mt-4">
                                        <span :class="['rounded-full px-3 py-1 text-xs font-medium capitalize', getRoleColor(user.role)]">
                                            {{ user.role }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-6 border-t border-gray-100 pt-6">
                                    <dl class="divide-y divide-gray-100">
                                        <div class="py-3 flex justify-between">
                                            <dt class="text-sm font-medium text-gray-500">Joined</dt>
                                            <dd class="text-sm text-gray-900">{{ formatDate(user.created_at) }}</dd>
                                        </div>
                                        <div class="py-3 flex justify-between">
                                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                                            <dd class="text-sm text-gray-900 capitalize">{{ user.status || 'Active' }}</dd>
                                        </div>
                                    </dl>
                                </div>
                                <div class="mt-6">
                                    <Link
                                        :href="route('admin.users.edit', user.id)"
                                        class="flex w-full items-center justify-center rounded-lg bg-bnhs-blue px-4 py-2 text-sm font-medium text-white hover:bg-bnhs-blue-600"
                                    >
                                        Edit User
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Logs -->
                    <div class="md:col-span-2">
                        <div class="overflow-hidden rounded-xl bg-white shadow">
                            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                                <h3 class="text-lg font-medium text-gray-900">Activity Log</h3>
                            </div>
                            <div class="p-6">
                                <div v-if="user.logs && user.logs.length > 0" class="flow-root">
                                    <ul role="list" class="-mb-8">
                                        <li v-for="(log, logIdx) in user.logs" :key="log.id">
                                            <div class="relative pb-8">
                                                <span v-if="logIdx !== user.logs.length - 1" class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 ring-8 ring-white">
                                                            <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                        <div>
                                                            <p class="text-sm text-gray-500">
                                                                {{ log.description }}
                                                            </p>
                                                        </div>
                                                        <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                            <time :datetime="log.created_at">{{ formatDate(log.created_at) }}</time>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div v-else class="text-center text-gray-500 py-8">
                                    No activity logs found.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
