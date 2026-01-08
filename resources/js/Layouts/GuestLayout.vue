<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gradient-to-br from-bnhs-blue-50 to-white pt-6 sm:justify-center sm:pt-0"
    >
        <div class="text-center">
            <Link href="/" class="flex flex-col items-center">
                <ApplicationLogo class="h-20 w-auto" />
                <h1 class="mt-3 text-xl font-semibold text-bnhs-blue">
                    Bato National High School
                </h1>
                <p class="text-sm text-gray-500">eDocument Request</p>
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-lg sm:max-w-md sm:rounded-lg border-t-4 border-bnhs-blue"
        >
            <slot />
        </div>

        <div class="mt-6 text-center">
            <p class="text-xs text-gray-400">
                &copy; {{ new Date().getFullYear() }} BNHS eDocument Request
            </p>
            <Link :href="route('login')" class="mt-1 block text-[10px] text-gray-300 hover:text-gray-400 transition">
                Admin
            </Link>
        </div>
    </div>
</template>

