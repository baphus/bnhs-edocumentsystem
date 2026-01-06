<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Track } from '@/types';

const props = defineProps<{
    track: Track;
}>();

const form = useForm({
    category: props.track.category,
    code: props.track.code,
    name: props.track.name,
    is_active: props.track.is_active,
});

const submit = () => {
    form.patch(route('admin.tracks.update', props.track.id));
};
</script>

<template>
    <Head title="Edit Track" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit Educational Track
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="category" value="Category" />
                                <TextInput
                                    id="category"
                                    v-model="form.category"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.category" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="code" value="Code" />
                                <TextInput
                                    id="code"
                                    v-model="form.code"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.code" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="name" value="Name (Description)" />
                                <TextInput
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <div class="flex items-center">
                                <input
                                    id="is_active"
                                    type="checkbox"
                                    v-model="form.is_active"
                                    class="rounded border-gray-300 text-bnhs-blue shadow-sm focus:ring-bnhs-blue"
                                />
                                <label for="is_active" class="ml-2 text-sm text-gray-600">Active</label>
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Save Changes</PrimaryButton>
                                <Link
                                    :href="route('admin.tracks.index')"
                                    class="rounded-md px-4 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100"
                                >
                                    Cancel
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
