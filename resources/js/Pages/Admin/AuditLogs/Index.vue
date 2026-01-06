<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ChevronDownIcon, ChevronUpIcon, MagnifyingGlassIcon } from '@heroicons/vue/20/solid';

// Props definition
const props = defineProps<{
    logs: {
        data: Array<any>;
        links: Array<any>;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        total: number;
    };
    filters: {
        search?: string;
        action?: string;
        role?: string;
        model_type?: string;
        start_date?: string;
        end_date?: string;
    };
    actions: string[];
}>();

// State
const search = ref(props.filters.search || '');
const filterAction = ref(props.filters.action || '');
const filterRole = ref(props.filters.role || '');
const filterModel = ref(props.filters.model_type || '');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

const expandedRows = ref<Set<number>>(new Set());

// Debounce helper
const debounce = (fn: Function, delay: number) => {
    let timeoutId: ReturnType<typeof setTimeout>;
    return (...args: any[]) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

// Search/Filter update
const updateParams = debounce(() => {
    router.get(route('admin.audit-logs.index'), {
        search: search.value,
        action: filterAction.value,
        role: filterRole.value,
        model_type: filterModel.value,
        start_date: startDate.value,
        end_date: endDate.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 300);

// Watchers
watch([search, filterAction, filterRole, filterModel, startDate, endDate], () => {
    updateParams();
});

// Row expansion
const toggleRow = (id: number) => {
    if (expandedRows.value.has(id)) {
        expandedRows.value.delete(id);
    } else {
        expandedRows.value.add(id);
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleString(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
};

const formatModelType = (model: string | null) => {
    if (!model) return '-';
    // Remove App\Models\ prefix and add spaces to CamelCase
    const name = model.split('\\').pop() || model;
    return name.replace(/([A-Z])/g, ' $1').trim();
};

const formatRole = (role: string | null) => {
    if (!role) return 'N/A';
    return role.charAt(0).toUpperCase() + role.slice(1);
};

// Helper to get unified changes list
const getChanges = (log: any) => {
    const oldVals = log.old_values || {};
    const newVals = log.new_values || {};
    const keys = new Set([...Object.keys(oldVals), ...Object.keys(newVals)]);
    const changes: { key: string; old: any; new: any }[] = [];

    keys.forEach(key => {
        // Skip updated_at if it's the only thing that changed (optional)
        
        let oldVal = oldVals[key];
        let newVal = newVals[key];
        
        // Simple equality check
        if (JSON.stringify(oldVal) !== JSON.stringify(newVal)) {
            changes.push({
                key: key,
                old: oldVal,
                new: newVal
            });
        }
    });

    return changes;
};

const formatValue = (val: any) => {
    if (val === null) return 'null';
    if (val === true) return 'true';
    if (val === false) return 'false';
    if (typeof val === 'object') return JSON.stringify(val);
    return val;
};
</script>

<template>
    <Head title="Audit Logs" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Audit Logs</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        
                        <!-- Filters -->
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                            <!-- Search -->
                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Search</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                                    </div>
                                    <input
                                        v-model="search"
                                        type="text"
                                        class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md"
                                        placeholder="User, ID, or Description..."
                                    />
                                </div>
                            </div>

                            <!-- Action Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Action</label>
                                <select v-model="filterAction" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="">All Actions</option>
                                    <option v-for="action in actions" :key="action" :value="action">{{ action }}</option>
                                </select>
                            </div>

                            <!-- Role Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Role</label>
                                <select v-model="filterRole" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="">All Roles</option>
                                    <option value="admin">Admin</option>
                                    <option value="registrar">Registrar</option>
                                    <option value="user">User</option>
                                    <option value="system">System</option>
                                </select>
                            </div>

                            <!-- Date Range -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input v-model="startDate" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">End Date</label>
                                <input v-model="endDate" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Details</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template v-for="log in logs.data" :key="log.id">
                                        <tr @click="toggleRow(log.id)" class="cursor-pointer hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ formatDate(log.created_at) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ log.user ? log.user.name : 'System' }}</div>
                                                <div class="text-xs text-gray-500">{{ formatRole(log.user_role) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                    :class="{
                                                        'bg-green-100 text-green-800': log.action === 'CREATE',
                                                        'bg-yellow-100 text-yellow-800': log.action === 'UPDATE',
                                                        'bg-red-100 text-red-800': log.action === 'DELETE',
                                                        'bg-blue-100 text-blue-800': log.action === 'LOGIN',
                                                        'bg-gray-100 text-gray-800': !['CREATE', 'UPDATE', 'DELETE', 'LOGIN'].includes(log.action)
                                                    }">
                                                    {{ log.action }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div class="font-medium text-gray-900">
                                                    {{ formatModelType(log.model_type) }}
                                                </div>
                                                <div class="text-xs text-gray-400" v-if="log.model_id">ID: {{ log.model_id }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" :title="log.description">
                                                {{ log.description }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button class="text-indigo-600 hover:text-indigo-900">
                                                    <ChevronUpIcon v-if="expandedRows.has(log.id)" class="h-5 w-5" />
                                                    <ChevronDownIcon v-else class="h-5 w-5" />
                                                </button>
                                            </td>
                                        </tr>
                                        <!-- Expanded Details -->
                                        <tr v-if="expandedRows.has(log.id)" class="bg-gray-50">
                                            <td colspan="6" class="px-6 py-4">
                                                <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-200">
                                                    
                                                    <!-- Metadata -->
                                                    <div class="flex flex-wrap gap-4 text-xs text-gray-500 mb-4 border-b border-gray-100 pb-2">
                                                        <div v-if="log.user_id"><strong>User ID:</strong> {{ log.user_id }}</div>
                                                        <div><strong>IP:</strong> {{ log.ip_address }}</div>
                                                        <div><strong>User Agent:</strong> {{ log.user_agent }}</div>
                                                    </div>

                                                    <!-- Changes View (Preferred) -->
                                                    <div v-if="log.action === 'UPDATE' && (log.old_values || log.new_values)" class="mb-2">
                                                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Changes</h4>
                                                        <div class="overflow-x-auto">
                                                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                                                <thead class="bg-gray-50">
                                                                    <tr>
                                                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Field</th>
                                                                        <th class="px-3 py-2 text-left font-medium text-gray-500">Old Value</th>
                                                                        <th class="px-3 py-2 text-left font-medium text-gray-500">New Value</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="divide-y divide-gray-200">
                                                                    <tr v-for="change in getChanges(log)" :key="change.key">
                                                                        <td class="px-3 py-2 font-mono text-gray-600">{{ change.key }}</td>
                                                                        <td class="px-3 py-2 text-red-600 bg-red-50">{{ formatValue(change.old) }}</td>
                                                                        <td class="px-3 py-2 text-green-600 bg-green-50">{{ formatValue(change.new) }}</td>
                                                                    </tr>
                                                                    <tr v-if="getChanges(log).length === 0">
                                                                        <td colspan="3" class="px-3 py-2 text-gray-500 italic">No detected changes or only metadata update.</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>

                                                    <!-- Snapshot View (Create/Delete) -->
                                                    <div v-else-if="log.new_values || log.old_values" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        <div v-if="log.old_values && Object.keys(log.old_values).length > 0">
                                                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Previous Data</h4>
                                                            <div class="bg-gray-50 p-2 rounded text-xs">
                                                                <div v-for="(val, key) in log.old_values" :key="key" class="flex justify-between py-1 border-b border-gray-100 last:border-0">
                                                                    <span class="font-medium text-gray-600">{{ key }}:</span>
                                                                    <span class="text-gray-800">{{ formatValue(val) }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div v-if="log.new_values && Object.keys(log.new_values).length > 0">
                                                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">New Data</h4>
                                                            <div class="bg-gray-50 p-2 rounded text-xs">
                                                                <div v-for="(val, key) in log.new_values" :key="key" class="flex justify-between py-1 border-b border-gray-100 last:border-0">
                                                                    <span class="font-medium text-gray-600">{{ key }}:</span>
                                                                    <span class="text-gray-800">{{ formatValue(val) }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div v-else class="text-sm text-gray-500 italic">
                                                        No additional details captured for this event.
                                                    </div>

                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                         <!-- Pagination -->
                         <div class="mt-4 flex flex-col sm:flex-row justify-between items-center gap-4" v-if="logs.data.length > 0">
                            <div class="text-sm text-gray-500">
                                Showing {{ logs.from }} to {{ logs.to }} of {{ logs.total }} logs
                            </div>
                            <div class="flex flex-wrap gap-1 justify-center">
                                <template v-for="(link, key) in logs.links" :key="key">
                                    <div v-if="link.url === null" 
                                        class="px-3 py-1 text-sm text-gray-400 border rounded bg-gray-100"
                                        v-html="link.label" />
                                    <Link v-else
                                        :href="link.url"
                                        class="px-3 py-1 text-sm border rounded hover:bg-gray-50"
                                        :class="{ 'bg-indigo-50 border-indigo-500 text-indigo-700': link.active, 'bg-white text-gray-700': !link.active }"
                                        v-html="link.label" />
                                </template>
                            </div>
                        </div>
                        <div v-else class="mt-8 text-center text-gray-500">
                            No logs found matching your criteria.
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
