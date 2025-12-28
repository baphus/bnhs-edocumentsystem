<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { DocumentType } from '@/types';

interface Props {
    documentType?: DocumentType;
}

const props = defineProps<Props>();

const isEditing = !!props.documentType;

const form = useForm({
    name: props.documentType?.name || '',
    category: props.documentType?.category || 'Official',
    description: props.documentType?.description || '',
    processing_days: props.documentType?.processing_days || 3,
    is_active: props.documentType?.is_active !== undefined ? props.documentType.is_active : true,
});

const categories = [
    { value: 'Official', label: 'Official' },
    { value: 'Informal', label: 'Informal' },
    { value: 'Certified', label: 'Certified' },
];

const submit = () => {
    if (isEditing && props.documentType) {
        form.put(route('admin.superadmin.document-types.update', props.documentType.id));
    } else {
        form.post(route('admin.superadmin.document-types.store'));
    }
};
</script>

<template>
    <Head :title="isEditing ? `Edit Document Type - ${documentType?.name}` : 'Create Document Type'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.superadmin.document-types.index')"
                    class="text-gray-500 hover:text-gray-700"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ isEditing ? 'Edit Document Type' : 'Create Document Type' }}
                </h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-xl bg-white p-6 shadow">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                type="text"
                                v-model="form.name"
                                required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                placeholder="e.g., Good Moral Certificate"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="category"
                                v-model="form.category"
                                required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                            >
                                <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                                    {{ cat.label }}
                                </option>
                            </select>
                            <p v-if="form.errors.category" class="mt-1 text-sm text-red-600">
                                {{ form.errors.category }}
                            </p>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">
                                Description
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                                placeholder="Optional description of the document type"
                            ></textarea>
                            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <div>
                            <label for="processing_days" class="block text-sm font-medium text-gray-700">
                                Processing Days <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="processing_days"
                                type="number"
                                v-model.number="form.processing_days"
                                required
                                min="1"
                                max="30"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bnhs-blue focus:ring-bnhs-blue"
                            />
                            <p class="mt-1 text-xs text-gray-500">
                                Number of days required to process this document type (1-30 days)
                            </p>
                            <p v-if="form.errors.processing_days" class="mt-1 text-sm text-red-600">
                                {{ form.errors.processing_days }}
                            </p>
                        </div>

                        <div class="flex items-center">
                            <input
                                id="is_active"
                                type="checkbox"
                                v-model="form.is_active"
                                class="h-4 w-4 rounded border-gray-300 text-bnhs-blue focus:ring-bnhs-blue"
                            />
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                Active
                            </label>
                            <p class="ml-2 text-xs text-gray-500">
                                (Inactive document types won't be available for requests)
                            </p>
                        </div>
                        <p v-if="form.errors.is_active" class="text-sm text-red-600">
                            {{ form.errors.is_active }}
                        </p>

                        <div class="flex items-center justify-end gap-4 border-t border-gray-100 pt-6">
                            <Link
                                :href="route('admin.superadmin.document-types.index')"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-lg bg-bnhs-blue px-4 py-2 text-sm font-medium text-white hover:bg-bnhs-blue-600 disabled:opacity-50"
                            >
                                {{ isEditing ? 'Update Document Type' : 'Create Document Type' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

