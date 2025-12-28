<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { User } from '@/types';
import { computed } from 'vue';

const props = defineProps<{
    user: User;
    roles: string[];
}>();

const roleLabels: Record<string, string> = {
    superadmin: 'Super Admin',
    registrar: 'Registrar',
};

const rolesWithLabels = computed(() => {
    return props.roles.map(role => ({
        value: role,
        label: roleLabels[role] || role,
    }));
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.role,
});

const submit = () => {
    form.patch(route('admin.users.update', props.user.id));
};
</script>

<template>
    <Head :title="`Edit User - ${user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('admin.users.index')" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Edit User
                </h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-xl bg-white p-6 shadow">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input
                                id="name"
                                type="text"
                                v-model="form.name"
                                required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                            />
                            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                            <select
                                id="role"
                                v-model="form.role"
                                required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                            >
                                <option v-for="role in rolesWithLabels" :key="role.value" :value="role.value">
                                    {{ role.label }}
                                </option>
                            </select>
                            <p v-if="form.errors.role" class="mt-1 text-sm text-red-600">{{ form.errors.role }}</p>
                        </div>

                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-sm font-medium text-gray-700">Change Password (optional)</p>
                            <p class="text-xs text-gray-500">Leave blank to keep current password</p>

                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                                    <input
                                        id="password"
                                        type="password"
                                        v-model="form.password"
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                    />
                                    <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                                </div>

                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                                    <input
                                        id="password_confirmation"
                                        type="password"
                                        v-model="form.password_confirmation"
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4 border-t border-gray-100 pt-6">
                            <Link
                                :href="route('admin.users.index')"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-lg bg-bnhs-blue px-4 py-2 text-sm font-medium text-white hover:bg-bnhs-blue-600 disabled:opacity-50"
                            >
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

