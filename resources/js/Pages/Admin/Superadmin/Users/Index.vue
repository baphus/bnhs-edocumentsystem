<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    status: string;
    last_login_at: string | null;
    created_at: string;
}

interface Props {
    users: {
        data: User[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    filters: {
        search?: string;
        role?: string;
        status?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const roleFilter = ref(props.filters.role || '');
const statusFilter = ref(props.filters.status || '');
const selectedUsers = ref<number[]>([]);

const applyFilters = () => {
    router.get(route('admin.users.index'), {
        search: search.value || undefined,
        role: roleFilter.value || undefined,
        status: statusFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const toggleSelect = (userId: number) => {
    const index = selectedUsers.value.indexOf(userId);
    if (index > -1) {
        selectedUsers.value.splice(index, 1);
    } else {
        selectedUsers.value.push(userId);
    }
};

const toggleSelectAll = () => {
    if (selectedUsers.value.length === props.users.data.length) {
        selectedUsers.value = [];
    } else {
        selectedUsers.value = props.users.data.map((u) => u.id);
    }
};

const impersonate = (userId: number) => {
    if (confirm('Are you sure you want to impersonate this user?')) {
        router.post(route('admin.users.impersonate', userId));
    }
};

const resetPassword = (userId: number) => {
    const password = prompt('Enter new password (min 8 characters):');
    if (password && password.length >= 8) {
        router.post(route('admin.users.reset-password', userId), {
            password,
            password_confirmation: password,
        });
    }
};

const bulkUpdateStatus = (status: string) => {
    if (selectedUsers.value.length === 0) {
        alert('Please select at least one user.');
        return;
    }
    if (confirm(`Update ${selectedUsers.value.length} users to ${status}?`)) {
        router.post(route('admin.users.bulk-status'), {
            user_ids: selectedUsers.value,
            status,
        });
    }
};
</script>

<template>
    <Head title="User Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    User Management
                </h2>
                <Link :href="route('admin.users.create')" class="rounded-md bg-bnhs-blue px-4 py-2 text-sm font-medium text-white hover:bg-bnhs-blue-600">
                    Create User
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-6 rounded-xl bg-white p-6 shadow">
                    <div class="grid gap-4 sm:grid-cols-4">
                        <div>
                            <TextInput
                                v-model="search"
                                type="text"
                                placeholder="Search by name or email..."
                                class="w-full"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <div>
                            <select
                                v-model="roleFilter"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                @change="applyFilters"
                            >
                                <option value="">All Roles</option>
                                <option value="superadmin">Superadmin</option>
                                <option value="registrar">Registrar</option>
                            </select>
                        </div>
                        <div>
                            <select
                                v-model="statusFilter"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                @change="applyFilters"
                            >
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                        <div>
                            <PrimaryButton @click="applyFilters" class="w-full">
                                Apply Filters
                            </PrimaryButton>
                        </div>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div v-if="selectedUsers.length > 0" class="mb-4 rounded-lg bg-bnhs-blue-50 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-bnhs-blue">
                            {{ selectedUsers.length }} user(s) selected
                        </span>
                        <div class="flex gap-2">
                            <PrimaryButton @click="bulkUpdateStatus('active')" size="sm">
                                Activate
                            </PrimaryButton>
                            <DangerButton @click="bulkUpdateStatus('suspended')" size="sm">
                                Suspend
                            </DangerButton>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="overflow-hidden rounded-xl bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input
                                        type="checkbox"
                                        :checked="selectedUsers.length === users.data.length && users.data.length > 0"
                                        @change="toggleSelectAll"
                                        class="rounded border-gray-300 text-bnhs-blue focus:ring-bnhs-blue"
                                    />
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Email
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Role
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Last Login
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <input
                                        type="checkbox"
                                        :checked="selectedUsers.includes(user.id)"
                                        @change="toggleSelect(user.id)"
                                        class="rounded border-gray-300 text-bnhs-blue focus:ring-bnhs-blue"
                                    />
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ user.name }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ user.email }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        :class="[
                                            'rounded-full px-2 py-1 text-xs font-medium',
                                            user.role === 'superadmin'
                                                ? 'bg-purple-100 text-purple-800'
                                                : 'bg-bnhs-blue-100 text-bnhs-blue-800',
                                        ]"
                                    >
                                        {{ user.role }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        :class="[
                                            'rounded-full px-2 py-1 text-xs font-medium',
                                            user.status === 'active'
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-red-100 text-red-800',
                                        ]"
                                    >
                                        {{ user.status }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ user.last_login_at ? new Date(user.last_login_at).toLocaleDateString() : 'Never' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            @click="impersonate(user.id)"
                                            class="text-bnhs-blue hover:text-bnhs-blue-600"
                                            title="Login as this user"
                                        >
                                            👤
                                        </button>
                                        <button
                                            @click="resetPassword(user.id)"
                                            class="text-yellow-600 hover:text-yellow-700"
                                            title="Reset password"
                                        >
                                            🔑
                                        </button>
                                        <Link
                                            :href="route('admin.users.show', user.id)"
                                            class="text-green-600 hover:text-green-700"
                                        >
                                            View
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    No users found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.last_page > 1" class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing page {{ users.current_page }} of {{ users.last_page }}
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in users.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-2 text-sm rounded-md',
                                link.active
                                    ? 'bg-bnhs-blue text-white'
                                    : 'bg-white text-gray-700 hover:bg-gray-50',
                                !link.url ? 'opacity-50 cursor-not-allowed' : '',
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>


