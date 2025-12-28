<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    canLogin?: boolean;
}>();
</script>

<template>
    <Head title="BNHS eDocument System" />
    
    <div class="min-h-screen bg-gradient-to-br from-bnhs-blue-50 via-white to-bnhs-gold-50">
        <!-- Navigation -->
        <nav class="fixed top-0 z-50 w-full border-b border-white/20 bg-white/80 backdrop-blur-md">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-bnhs-blue shadow-lg">
                            <span class="text-sm font-bold text-white">BNHS</span>
                        </div>
                        <div class="hidden sm:block">
                            <p class="text-sm font-semibold text-bnhs-blue">eDocument System</p>
                            <p class="text-xs text-gray-500">Bato National High School</p>
                        </div>
                    </div>
                    
                    <div v-if="canLogin" class="flex items-center gap-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('admin.dashboard')"
                            class="rounded-lg bg-bnhs-blue px-4 py-2 text-sm font-medium text-white shadow-md transition hover:bg-bnhs-blue-600"
                        >
                            Dashboard
                        </Link>
                        <Link
                            v-else
                            :href="route('login')"
                            class="text-sm font-medium text-gray-600 hover:text-bnhs-blue"
                        >
                            Admin Login
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative overflow-hidden pt-16">
            <div class="absolute inset-0 -z-10">
                <div class="absolute left-1/2 top-0 h-[500px] w-[500px] -translate-x-1/2 rounded-full bg-bnhs-blue/5 blur-3xl"></div>
                <div class="absolute right-0 top-1/2 h-[300px] w-[300px] rounded-full bg-bnhs-gold/10 blur-3xl"></div>
            </div>
            
            <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-32 lg:px-8">
                <div class="text-center">
                    <div class="mb-6 inline-flex items-center rounded-full bg-bnhs-blue/10 px-4 py-2 text-sm font-medium text-bnhs-blue">
                        <svg class="mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        No Account Needed
                    </div>
                    
                    <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl md:text-6xl">
                        <span class="block">Bato National High School</span>
                        <span class="block text-bnhs-blue">eDocument System</span>
                    </h1>
                    
                    <p class="mx-auto mt-6 max-w-2xl text-lg text-gray-600">
                        Request and track your school documents online. No login required — 
                        just verify your email with a one-time code and submit your request.
                    </p>

                    <!-- Quick Actions -->
                    <div class="mx-auto mt-10 flex max-w-xl flex-col items-center gap-4 sm:flex-row sm:justify-center">
                        <Link
                            :href="route('request.select')"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-bnhs-blue px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:bg-bnhs-blue-600 hover:shadow-xl sm:w-auto"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Request a Document
                        </Link>
                        <Link
                            :href="route('track.index')"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-bnhs-blue bg-white px-8 py-4 text-base font-semibold text-bnhs-blue shadow-lg transition hover:bg-bnhs-blue-50 sm:w-auto"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Track Your Request
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Available Documents</h2>
                    <p class="mt-2 text-gray-600">Request any of these documents online</p>
                </div>

                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Official Documents -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-lg">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-bnhs-blue/10">
                            <svg class="h-6 w-6 text-bnhs-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900">Official Documents</h3>
                        <ul class="mt-3 space-y-2 text-sm text-gray-600">
                            <li class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-bnhs-blue"></span>
                                Good Moral Certificate
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-bnhs-blue"></span>
                                Enrollment Certificate
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-bnhs-blue"></span>
                                Report Card (Form 138)
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-bnhs-blue"></span>
                                Permanent Record (Form 137)
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-bnhs-blue"></span>
                                Diploma
                            </li>
                        </ul>
                    </div>

                    <!-- Informal Documents -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-lg">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-bnhs-gold/20">
                            <svg class="h-6 w-6 text-bnhs-gold-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900">Informal Documents</h3>
                        <ul class="mt-3 space-y-2 text-sm text-gray-600">
                            <li class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-bnhs-gold"></span>
                                Grade Slip (Quarter 1)
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-bnhs-gold"></span>
                                Grade Slip (Quarter 2)
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-bnhs-gold"></span>
                                Grade Slip (Quarter 3)
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-bnhs-gold"></span>
                                Grade Slip (Quarter 4)
                            </li>
                        </ul>
                    </div>

                    <!-- Certified Documents -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-lg">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-100">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900">Certified Documents</h3>
                        <ul class="mt-3 space-y-2 text-sm text-gray-600">
                            <li class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                Certified True Copy of Report Card
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                Certified True Copy of Diploma
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                CAV Documents
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="bg-bnhs-blue py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-white sm:text-3xl">How It Works</h2>
                    <p class="mt-2 text-bnhs-blue-200">Simple steps to request your documents — no account needed!</p>
                </div>

                <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white text-2xl font-bold text-bnhs-blue shadow-lg">
                            1
                        </div>
                        <h3 class="mt-4 font-semibold text-white">Select Document</h3>
                        <p class="mt-2 text-sm text-bnhs-blue-200">Choose the type of document you need</p>
                    </div>
                    <div class="text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white text-2xl font-bold text-bnhs-blue shadow-lg">
                            2
                        </div>
                        <h3 class="mt-4 font-semibold text-white">Verify Email</h3>
                        <p class="mt-2 text-sm text-bnhs-blue-200">Enter your email and verify with OTP code</p>
                    </div>
                    <div class="text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white text-2xl font-bold text-bnhs-blue shadow-lg">
                            3
                        </div>
                        <h3 class="mt-4 font-semibold text-white">Submit Request</h3>
                        <p class="mt-2 text-sm text-bnhs-blue-200">Fill out the form and get your tracking ID</p>
                    </div>
                    <div class="text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-bnhs-gold text-2xl font-bold text-bnhs-blue shadow-lg">
                            4
                        </div>
                        <h3 class="mt-4 font-semibold text-white">Pickup Document</h3>
                        <p class="mt-2 text-sm text-bnhs-blue-200">Get notified when ready and pick up</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-gray-200 bg-white py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-bnhs-blue">
                            <span class="text-sm font-bold text-white">BNHS</span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Bato National High School</p>
                            <p class="text-xs text-gray-500">Toledo City, Cebu</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">
                        &copy; {{ new Date().getFullYear() }} BNHS eDocument System. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
