<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import SidebarLink from '@/Components/SidebarLink.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';

const sidebarCollapsed = ref(false);
const mobileSidebarOpen = ref(false);

const page = usePage();
const user = computed(() => page.props.auth.user);
// Include legacy roles for robustness
const isRegistrar = computed(() => ['registrar', 'principal'].includes(user.value.role));
const isAdmin = computed(() => ['admin', 'superadmin'].includes(user.value.role));

const getRoleBadge = (role: string) => {
    const badges: Record<string, { text: string; class: string }> = {
        registrar: { text: 'Registrar', class: 'bg-bnhs-blue-100 text-bnhs-blue-800' },
        principal: { text: 'Principal', class: 'bg-bnhs-gold-100 text-bnhs-gold-800' }, // Legacy
        guest: { text: 'Guest', class: 'bg-green-100 text-green-800' },
        student: { text: 'Student', class: 'bg-green-100 text-green-800' }, // Legacy
        admin: { text: 'Admin', class: 'bg-purple-100 text-purple-800' },
        superadmin: { text: 'Superadmin', class: 'bg-purple-100 text-purple-800' }, // Legacy
    };
    return badges[role] || { text: role, class: 'bg-gray-100 text-gray-800' };
};

const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    // Save preference to localStorage
    localStorage.setItem('sidebarCollapsed', String(sidebarCollapsed.value));
};

const closeMobileSidebar = () => {
    mobileSidebarOpen.value = false;
};

const toggleMobileSidebar = () => {
    mobileSidebarOpen.value = !mobileSidebarOpen.value;
};

// Load sidebar state from localStorage
onMounted(() => {
    const saved = localStorage.getItem('sidebarCollapsed');
    if (saved !== null) {
        sidebarCollapsed.value = saved === 'true';
    }
});

// Close mobile sidebar on escape key
const handleEscape = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && mobileSidebarOpen.value) {
        closeMobileSidebar();
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleEscape);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleEscape);
});
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Mobile Sidebar Overlay -->
        <Transition
            enter-active-class="transition-opacity ease-linear duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-linear duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-show="mobileSidebarOpen"
                class="fixed inset-0 z-40 bg-gray-900 bg-opacity-50 lg:hidden"
                @click="closeMobileSidebar"
            ></div>
        </Transition>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed left-0 top-0 z-50 h-screen bg-white border-r border-gray-200 transition-all duration-300 ease-in-out',
                sidebarCollapsed ? 'w-20' : 'w-64',
                mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
        >
            <div class="flex h-full flex-col">
                <!-- Logo Section -->
                <div class="flex h-16 items-center justify-between border-b border-gray-200 px-4">
                    <Link
                        :href="route('dashboard')"
                        class="flex items-center gap-3"
                        :class="sidebarCollapsed ? 'justify-center' : ''"
                    >
                        <ApplicationLogo class="h-10 w-auto shrink-0" />
                        <div v-if="!sidebarCollapsed" class="hidden lg:block">
                            <p class="text-sm font-semibold text-bnhs-blue">eDocument System</p>
                            <p class="text-xs text-gray-500">Bato National High School</p>
                        </div>
                    </Link>
                    <!-- Mobile close button -->
                    <button
                        @click="closeMobileSidebar"
                        class="lg:hidden rounded-md p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-500"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation Menu -->
                <nav class="flex-1 overflow-y-auto px-3 py-4">
                    <div class="space-y-1">
                        <!-- Dashboard -->
                        <SidebarLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard') || route().current('registrar.dashboard') || route().current('admin.dashboard')"
                            :collapsed="sidebarCollapsed"
                            icon="dashboard"
                        >
                            Dashboard
                        </SidebarLink>

                        <!-- Requests -->
                        <SidebarLink
                            v-if="isAdmin || isRegistrar"
                            :href="isAdmin ? route('admin.requests.index') : route('registrar.requests.index')"
                            :active="route().current('registrar.requests.index') || route().current('registrar.requests.show') || route().current('admin.requests.index')"
                            :collapsed="sidebarCollapsed"
                            icon="requests"
                        >
                            Requests
                        </SidebarLink>

                        <!-- Users (Admin only) -->
                        <SidebarLink
                            v-if="isAdmin"
                            :href="route('admin.users.index')"
                            :active="route().current('admin.users.index') || route().current('admin.users.show')"
                            :collapsed="sidebarCollapsed"
                            icon="users"
                        >
                            Users
                        </SidebarLink>

                        <!-- Document Types (Admin only) -->
                        <SidebarLink
                            v-if="isAdmin"
                            :href="route('admin.document-types.index')"
                            :active="route().current('admin.document-types.index') || route().current('admin.document-types.create') || route().current('admin.document-types.edit')"
                            :collapsed="sidebarCollapsed"
                            icon="document-types"
                        >
                            Document Types
                        </SidebarLink>

                        <!-- System Logs (Admin only) -->
                        <SidebarLink
                            v-if="isAdmin"
                            :href="route('admin.logs.index')"
                            :active="route().current('admin.logs.index')"
                            :collapsed="sidebarCollapsed"
                            icon="logs"
                        >
                            System Logs
                        </SidebarLink>

                        <!-- Settings (Admin only) -->
                        <SidebarLink
                            v-if="isAdmin"
                            :href="route('admin.settings.index')"
                            :active="route().current('admin.settings.index')"
                            :collapsed="sidebarCollapsed"
                            icon="settings"
                        >
                            Settings
                        </SidebarLink>
                    </div>
                </nav>

                <!-- User Section -->
                <div class="border-t border-gray-200 p-4">
                    <div class="flex items-center gap-3" :class="sidebarCollapsed ? 'justify-center' : ''">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-200">
                            <span class="text-sm font-medium text-gray-700">
                                {{ user.name.charAt(0).toUpperCase() }}
                            </span>
                        </div>
                        <div v-if="!sidebarCollapsed" class="hidden lg:block flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="truncate text-sm font-medium text-gray-900">{{ user.name }}</p>
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium shrink-0"
                                    :class="getRoleBadge(user.role).class"
                                >
                                    {{ getRoleBadge(user.role).text }}
                                </span>
                            </div>
                            <p class="truncate text-xs text-gray-500">{{ user.email || 'Student Account' }}</p>
                        </div>
                    </div>
                    <div v-if="!sidebarCollapsed" class="hidden lg:block mt-3 space-y-1">
                        <SidebarLink
                            :href="route('profile.edit')"
                            :active="route().current('profile.edit')"
                            :collapsed="sidebarCollapsed"
                            icon="profile"
                        >
                            Profile
                        </SidebarLink>
                    </div>
                </div>

                <!-- Collapse Toggle (Desktop only) -->
                <div class="hidden lg:block border-t border-gray-200 p-2">
                    <button
                        @click="toggleSidebar"
                        class="flex w-full items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 transition-colors"
                        :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                    >
                        <svg
                            v-if="!sidebarCollapsed"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                        </svg>
                        <svg
                            v-else
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div :class="['transition-all duration-300 ease-in-out', sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64']">
            <!-- Top Bar -->
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 shadow-sm">
                <div class="flex items-center gap-4">
                    <!-- Mobile menu button -->
                    <button
                        @click="toggleMobileSidebar"
                        class="lg:hidden rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Page Title -->
                    <div v-if="$slots.header" class="text-lg font-semibold text-gray-900">
                        <slot name="header" />
                    </div>
                </div>

                <!-- User Menu (Desktop) -->
                <div class="hidden lg:flex lg:items-center lg:gap-4">
                    <!-- Role Badge -->
                    <span
                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                        :class="getRoleBadge(user.role).class"
                    >
                        {{ getRoleBadge(user.role).text }}
                    </span>

                    <!-- User Dropdown -->
                    <div class="relative">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <span class="inline-flex rounded-md">
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                    >
                                        {{ user.name }}
                                        <svg
                                            class="-me-0.5 ms-2 h-4 w-4"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </span>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile.edit')">
                                    Profile
                                </DropdownLink>
                                <DropdownLink
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                >
                                    Log Out
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>

                <!-- Mobile User Menu -->
                <div class="lg:hidden">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200">
                                <span class="text-sm font-medium text-gray-700">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </span>
                            </button>
                        </template>

                        <template #content>
                            <div class="px-4 py-3 border-b border-gray-200">
                                <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                                <p class="text-xs text-gray-500">{{ user.email || 'Student Account' }}</p>
                                <span
                                    class="mt-1 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="getRoleBadge(user.role).class"
                                >
                                    {{ getRoleBadge(user.role).text }}
                                </span>
                            </div>
                            <DropdownLink :href="route('profile.edit')">
                                Profile
                            </DropdownLink>
                            <DropdownLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Page Content -->
            <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot />
            </main>

            <!-- Footer -->
            <footer class="border-t border-gray-200 bg-white py-4">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <p class="text-center text-xs text-gray-500">
                        &copy; {{ new Date().getFullYear() }} BNHS eDocument System - Bato National High School
                    </p>
                </div>
            </footer>
        </div>
    </div>
</template>

