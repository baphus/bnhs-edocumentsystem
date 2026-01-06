<script setup lang="ts">
interface Activity {
    id: number;
    action: string;
    user_name: string;
    tracking_id?: string;
    old_value?: string;
    new_value?: string;
    description?: string;
    created_at: string;
}

interface Props {
    activities: Activity[];
}

const props = defineProps<Props>();

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getActionIcon = (action: string) => {
    const icons: Record<string, string> = {
        status_change: '🔄',
        note_updated: '📝',
        password_reset: '🔑',
        user_deleted: '🗑️',
        request_deleted: '🗑️',
        impersonate: '👤',
        bulk_delete: '🗑️',
        bulk_resend_otp: '📧',
    };
    return icons[action] || '📋';
};

const getActionColor = (action: string) => {
    if (action.includes('delete')) return 'text-red-600 bg-red-50';
    if (action.includes('reset') || action.includes('impersonate')) return 'text-blue-600 bg-blue-50';
    if (action.includes('status')) return 'text-green-600 bg-green-50';
    return 'text-gray-600 bg-gray-50';
};
</script>

<template>
    <div class="rounded-xl bg-white shadow">
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-gray-900">Activity Feed</h3>
        </div>
        <div class="divide-y divide-gray-200">
            <div
                v-for="activity in activities"
                :key="activity.id"
                class="px-6 py-4 hover:bg-gray-50"
            >
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm"
                        :class="getActionColor(activity.action)"
                    >
                        {{ getActionIcon(activity.action) }}
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-900">
                            <span class="font-medium">{{ activity.user_name + ' '}}</span>
                            <span v-if="activity.action === 'status_change'">
                                changed request
                                <span v-if="activity.tracking_id" class="font-mono text-bnhs-blue">
                                    {{ activity.tracking_id }}
                                </span>
                                status from
                                <span class="font-medium">{{ activity.old_value }}</span>
                                to
                                <span class="font-medium">{{ activity.new_value }}</span>
                            </span>
                            <span v-else-if="activity.description">
                                {{ activity.description }}
                            </span>
                            <span v-else>
                                {{ activity.action.replace('_', ' ') }}
                            </span>
                        </p>
                        <p class="mt-1 text-xs text-gray-500">{{ formatDate(activity.created_at) }}</p>
                    </div>
                </div>
            </div>
            <div v-if="activities.length === 0" class="px-6 py-8 text-center text-gray-500">
                No activities yet
            </div>
        </div>
    </div>
</template>


