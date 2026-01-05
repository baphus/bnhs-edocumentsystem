<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';
import { DocumentType } from '@/types';

interface Props {
    documentTypes: DocumentType[];
    filters: {
        search?: string;
        category?: string;
        status?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const categoryFilter = ref(props.filters.category || '');
const statusFilter = ref(props.filters.status || '');
const showFilters = ref(false);

const showDeleteModal = ref(false);
const documentTypeToDelete = ref<DocumentType | null>(null);

const applyFilters = () => {
    router.get(route('admin.document-types.index'), {
        search: search.value || undefined,
        category: categoryFilter.value || undefined,
        status: statusFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const confirmDelete = (documentType: DocumentType) => {
    documentTypeToDelete.value = documentType;
    showDeleteModal.value = true;
};

const deleteDocumentType = () => {
    if (documentTypeToDelete.value) {
        router.delete(route('admin.document-types.destroy', documentTypeToDelete.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false;
                documentTypeToDelete.value = null;
            },
        });
    }
};

const getCategoryColor = (category: string) => {
    const colors: Record<string, string> = {
        'Official': 'bg-blue-100 text-blue-800',
        'Informal': 'bg-green-100 text-green-800',
        'Certified': 'bg-purple-100 text-purple-800',
    };
    return colors[category] || 'bg-gray-100 text-gray-800';
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Document Types" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Document Types
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-6 rounded-xl bg-white p-6 shadow">
                    <div class="flex flex-col gap-4">
                        <!-- Top Row: Search and Actions -->
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="flex-1 max-w-md">
                                <TextInput
                                    v-model="search"
                                    type="text"
                                    placeholder="Search by name..."
                                    class="w-full"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                            <div class="flex items-center gap-2">
                                <SecondaryButton @click="showFilters = !showFilters" class="whitespace-nowrap">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                    </svg>
                                    Filters
                                    <svg class="ml-2 h-4 w-4" :class="{ 'rotate-180': showFilters }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </SecondaryButton>

                                <Link
                                    :href="route('admin.document-types.create')"
                                    class="inline-flex items-center rounded-md border border-transparent bg-bnhs-blue px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-bnhs-blue-600 focus:bg-bnhs-blue-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-bnhs-blue-700"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add Document Type
                                </Link>
                            </div>
                        </div>

                        <!-- Expandable Filters -->
                        <div v-if="showFilters" class="pt-4 border-t border-gray-200">
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                    <select
                                        v-model="categoryFilter"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                        @change="applyFilters"
                                    >
                                        <option value="">All Categories</option>
                                        <option value="Official">Official</option>
                                        <option value="Informal">Informal</option>
                                        <option value="Certified">Certified</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select
                                        v-model="statusFilter"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                        @change="applyFilters"
                                    >
                                        <option value="">All Status</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <PrimaryButton @click="applyFilters" class="w-full justify-center">
                                        Apply Filters
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Document Types Table -->
                <div class="overflow-hidden rounded-xl bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Category
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Description
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Processing Days
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Requests
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Created
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="documentType in documentTypes" :key="documentType.id" class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ documentType.name }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        :class="[
                                            'rounded-full px-2 py-1 text-xs font-medium',
                                            getCategoryColor(documentType.category),
                                        ]"
                                    >
                                        {{ documentType.category }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <span v-if="documentType.description" class="line-clamp-2 max-w-xs">
                                        {{ documentType.description }}
                                    </span>
                                    <span v-else class="text-gray-400 italic">No description</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ documentType.processing_days || 3 }} day(s)
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        :class="[
                                            'rounded-full px-2 py-1 text-xs font-medium',
                                            documentType.is_active
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-red-100 text-red-800',
                                        ]"
                                    >
                                        {{ documentType.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ documentType.requests_count || 0 }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ formatDate(documentType.created_at) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <Link
                                            :href="route('admin.document-types.edit', documentType.id)"
                                            class="text-bnhs-blue hover:text-bnhs-blue-600"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="confirmDelete(documentType)"
                                            class="text-red-600 hover:text-red-700"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="documentTypes.length === 0">
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                    No document types found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            @click.self="showDeleteModal = false"
        >
            <div class="rounded-lg bg-white p-6 shadow-xl max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Delete Document Type</h3>
                <p class="text-sm text-gray-600 mb-6">
                    Are you sure you want to delete "<strong>{{ documentTypeToDelete?.name }}</strong>"? 
                    This action cannot be undone.
                </p>
                <div class="flex justify-end gap-3">
                    <button
                        @click="showDeleteModal = false"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <DangerButton @click="deleteDocumentType">
                        Delete
                    </DangerButton>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>


