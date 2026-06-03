<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    categories: Array<{
        id: number;
        name: string;
        icon: string;
        color: string;
        type: 'expense' | 'income';
        is_default: boolean;
        user_id: number | null;
    }>;
}>();

const expenses = computed(() => props.categories.filter(c => c.type === 'expense'));
const incomes  = computed(() => props.categories.filter(c => c.type === 'income'));

function remove(id: number) {
    if (!confirm('Supprimer cette catégorie ? Les transactions associées perdront leur catégorie.')) return;
    router.delete(route('categories.destroy', id));
}
</script>

<template>
    <Head title="Catégories" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Catégories</h2>
                <Link :href="route('categories.create')" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    + Nouvelle
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl space-y-8 sm:px-6 lg:px-8">

                <!-- Section Dépenses -->
                <div>
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500">Dépenses</h3>
                    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr class="text-left text-xs font-medium uppercase text-gray-500">
                                    <th class="px-4 py-3">Catégorie</th>
                                    <th class="px-4 py-3">Couleur</th>
                                    <th class="px-4 py-3">Origine</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-if="expenses.length === 0">
                                    <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">Aucune catégorie.</td>
                                </tr>
                                <tr v-for="c in expenses" :key="c.id" class="text-sm">
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-2">
                                            <span class="inline-block h-3 w-3 rounded-full" :style="{ backgroundColor: c.color }"></span>
                                            <span class="font-medium text-gray-800">{{ c.name }}</span>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-1.5 text-xs text-gray-500">
                                            <span class="h-4 w-4 rounded border border-gray-200" :style="{ backgroundColor: c.color }"></span>
                                            {{ c.color }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span v-if="c.user_id === null" class="text-xs text-gray-400 italic">Défaut système</span>
                                        <span v-else class="text-xs text-indigo-600 font-medium">Personnalisée</span>
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <template v-if="c.user_id !== null">
                                            <Link :href="route('categories.edit', c.id)" class="text-blue-600 hover:underline mr-3">Éditer</Link>
                                            <button @click="remove(c.id)" class="text-red-600 hover:underline">Suppr.</button>
                                        </template>
                                        <span v-else class="text-xs text-gray-300">—</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section Revenus -->
                <div>
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500">Revenus</h3>
                    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr class="text-left text-xs font-medium uppercase text-gray-500">
                                    <th class="px-4 py-3">Catégorie</th>
                                    <th class="px-4 py-3">Couleur</th>
                                    <th class="px-4 py-3">Origine</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-if="incomes.length === 0">
                                    <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">Aucune catégorie.</td>
                                </tr>
                                <tr v-for="c in incomes" :key="c.id" class="text-sm">
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-2">
                                            <span class="inline-block h-3 w-3 rounded-full" :style="{ backgroundColor: c.color }"></span>
                                            <span class="font-medium text-gray-800">{{ c.name }}</span>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-1.5 text-xs text-gray-500">
                                            <span class="h-4 w-4 rounded border border-gray-200" :style="{ backgroundColor: c.color }"></span>
                                            {{ c.color }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span v-if="c.user_id === null" class="text-xs text-gray-400 italic">Défaut système</span>
                                        <span v-else class="text-xs text-indigo-600 font-medium">Personnalisée</span>
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <template v-if="c.user_id !== null">
                                            <Link :href="route('categories.edit', c.id)" class="text-blue-600 hover:underline mr-3">Éditer</Link>
                                            <button @click="remove(c.id)" class="text-red-600 hover:underline">Suppr.</button>
                                        </template>
                                        <span v-else class="text-xs text-gray-300">—</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <p class="text-xs text-gray-400">
                    Les catégories « Défaut système » sont partagées avec tous les utilisateurs et ne peuvent pas être modifiées.
                </p>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
