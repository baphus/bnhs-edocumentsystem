<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const form = useForm({
    tracking_id: '',
    email: '',
});

const formatTrackingId = (e: Event) => {
    const input = e.target as HTMLInputElement;
    let value = input.value.toUpperCase().replace(/[^A-Z0-9-]/g, '');
    form.tracking_id = value;
};

const trackRequest = () => {
    form.post(route('track.track'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Track Request" />

    <div class="min-h-screen bg-gradient-to-br from-bnhs-blue-50 via-white to-bnhs-gold-50">
        <!-- Header -->
        <nav class="border-b border-gray-200 bg-white/80 backdrop-blur-md">
            <div class="mx-auto max-w-4xl px-4 py-4">
                <div class="flex items-center justify-between">
                    <Link :href="route('home')" class="flex items-center gap-3">
                        <ApplicationLogo class="h-10 w-auto" />
                        <span class="font-semibold text-gray-900">eDocument Request</span>
                    </Link>
                    <Link :href="route('home')" class="text-sm text-gray-600 hover:text-bnhs-blue">
                        ← Back to Home
                    </Link>
                </div>
            </div>
        </nav>

        <div class="mx-auto max-w-2xl px-4 py-12">
            <!-- Search Form -->
            <div class="rounded-2xl bg-white p-8 shadow-xl">
                <div class="text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-bnhs-blue-100">
                        <svg class="h-8 w-8 text-bnhs-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <h1 class="mt-4 text-2xl font-bold text-gray-900">Track Your Request</h1>
                    <p class="mt-2 text-gray-600">
                        Enter your tracking ID and email to view your request details
                    </p>
                </div>

                <!-- Tracking Form -->
                <form @submit.prevent="trackRequest" class="mt-8">
                    <div class="space-y-4">
                        <div>
                            <label for="tracking_id" class="block text-sm font-medium text-gray-700">Tracking ID</label>
                            <input
                                id="tracking_id"
                                type="text"
                                :value="form.tracking_id"
                                @input="formatTrackingId"
                                required
                                placeholder="BNHS-XXXXXXXX"
                                class="mt-1 block w-full rounded-lg border-gray-300 py-3 pl-4 pr-4 text-lg font-mono uppercase shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                            />
                            <p v-if="form.errors.tracking_id" class="mt-1 text-sm text-red-600">{{ form.errors.tracking_id }}</p>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                required
                                placeholder="your.email@gmail.com"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                            />
                            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing || !form.tracking_id || !form.email"
                        class="mt-6 w-full rounded-xl bg-bnhs-blue py-3 font-semibold text-white transition hover:bg-bnhs-blue-600 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span v-if="form.processing">Loading...</span>
                        <span v-else>Track Request</span>
                    </button>
                </form>
            </div>

            <!-- Help Section -->
            <div class="mt-8 text-center text-sm text-gray-600">
                <p>Lost your tracking ID? Check your email for the confirmation message.</p>
                <p class="mt-1">Need help? Contact the registrar's office.</p>
                <p class="mt-1">📧 registrar@bnhs.edu.ph</p>
            </div>
        </div>
    </div>
</template>

