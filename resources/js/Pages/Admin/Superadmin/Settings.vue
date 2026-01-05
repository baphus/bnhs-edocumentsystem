<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

interface Props {
    settings: {
        reminder_interval_days: number;
        max_reminder_count: number;
        otp_expiry_minutes: number;
        school_year_current: string;
        maintenance_mode: boolean;
    };
}

const props = defineProps<Props>();

const form = useForm({
    reminder_interval_days: props.settings.reminder_interval_days.toString(),
    max_reminder_count: props.settings.max_reminder_count.toString(),
    otp_expiry_minutes: props.settings.otp_expiry_minutes.toString(),
    school_year_current: props.settings.school_year_current,
    maintenance_mode: props.settings.maintenance_mode,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        reminder_interval_days: parseInt(data.reminder_interval_days as string, 10),
        max_reminder_count: parseInt(data.max_reminder_count as string, 10),
        otp_expiry_minutes: parseInt(data.otp_expiry_minutes as string, 10),
    })).patch(route('admin.settings.update'));
};
</script>

<template>
    <Head title="System Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                System Settings
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6 rounded-xl bg-white p-6 shadow">
                    <!-- Reminder Settings -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Reminder Settings</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <InputLabel for="reminder_interval_days" value="Reminder Interval (Days)" />
                                <TextInput
                                    id="reminder_interval_days"
                                    v-model="form.reminder_interval_days"
                                    type="number"
                                    min="1"
                                    max="30"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.reminder_interval_days" />
                                <p class="mt-1 text-sm text-gray-500">
                                    Number of days between reminder emails for pending requests
                                </p>
                            </div>

                            <div>
                                <InputLabel for="max_reminder_count" value="Max Reminder Count" />
                                <TextInput
                                    id="max_reminder_count"
                                    v-model="form.max_reminder_count"
                                    type="number"
                                    min="1"
                                    max="10"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.max_reminder_count" />
                                <p class="mt-1 text-sm text-gray-500">
                                    Maximum number of reminder emails to send per request
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- OTP Settings -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">OTP Settings</h3>
                        
                        <div>
                            <InputLabel for="otp_expiry_minutes" value="OTP Expiry (Minutes)" />
                            <TextInput
                                id="otp_expiry_minutes"
                                v-model="form.otp_expiry_minutes"
                                type="number"
                                min="5"
                                max="60"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.otp_expiry_minutes" />
                            <p class="mt-1 text-sm text-gray-500">
                                How long OTP codes remain valid (5-60 minutes)
                            </p>
                        </div>
                    </div>

                    <!-- General Settings -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">General Settings</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <InputLabel for="school_year_current" value="Current School Year" />
                                <TextInput
                                    id="school_year_current"
                                    v-model="form.school_year_current"
                                    type="text"
                                    placeholder="2024-2025"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.school_year_current" />
                                <p class="mt-1 text-sm text-gray-500">
                                    Current academic year (e.g., 2024-2025)
                                </p>
                            </div>

                            <div class="flex items-center">
                                <input
                                    id="maintenance_mode"
                                    v-model="form.maintenance_mode"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-bnhs-blue focus:ring-bnhs-blue"
                                />
                                <InputLabel for="maintenance_mode" value="Maintenance Mode" class="ml-2" />
                            </div>
                            <p class="text-sm text-gray-500">
                                Enable maintenance mode to temporarily disable the system
                            </p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <PrimaryButton :disabled="form.processing">
                            Save Settings
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>


