<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { fcfa, fdatetime, paymentLabel } from '@/format';
import { ref } from 'vue';

const props = defineProps<{
    transactions: {
        data: Array<any>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    categories: Array<{ id: number; name: string; type: string; color: string }>;
    filters: { type?: string; category_id?: number; from?: string; to?: string };
}>();

const filterForm = ref({ ...props.filters });

function applyFilters() {
    router.get(route('transactions.index'), filterForm.value, { preserveState: true, replace: true });
}

function reset() {
    filterForm.value = { type: '', category_id: undefined, from: '', to: '' };
    applyFilters();
}

function remove(id: number) {
    if (!confirm('Supprimer cette transaction ?')) return;
    router.delete(route('transactions.destroy', id));
}
</script>

<template>
    <Head title="Transactions" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Transactions</h2>
                <Link :href="route('transactions.create')" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    + Nouvelle
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
                <!-- Filtres -->
                <div class="rounded-lg bg-white p-4 shadow-sm grid grid-cols-2 sm:grid-cols-5 gap-3">
                    <select v-model="filterForm.type" @change="applyFilters" class="rounded-md border-gray-300 text-sm">
                        <option value="">Tous types</option>
                        <option value="expense">Dépense</option>
                        <option value="income">Revenu</option>
                    </select>
                    <select v-model="filterForm.category_id" @change="applyFilters" class="rounded-md border-gray-300 text-sm">
                        <option :value="undefined">Toutes catégories</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <input v-model="filterForm.from" @change="applyFilters" type="date" class="rounded-md border-gray-300 text-sm" />
                    <input v-model="filterForm.to" @change="applyFilters" type="date" class="rounded-md border-gray-300 text-sm" />
                    <button @click="reset" class="rounded-md bg-gray-100 text-sm text-gray-700 hover:bg-gray-200">Réinitialiser</button>
                </div>

                <!-- Liste -->
                <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-medium uppercase text-gray-500">
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Catégorie</th>
                                <th class="px-4 py-3">Mode</th>
                                <th class="px-4 py-3">Note</th>
                                <th class="px-4 py-3 text-right">Montant</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="transactions.data.length === 0">
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                                    Aucune transaction trouvée.
                                </td>
                            </tr>
                            <tr v-for="tx in transactions.data" :key="tx.id" class="text-sm">
                                <td class="px-4 py-3 text-gray-700 whitespace-nowrap">{{ fdatetime(tx.occurred_at) }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: tx.category?.color || '#94a3b8' }"></span>
                                        {{ tx.category?.name || 'Sans catégorie' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ paymentLabel(tx.payment_method) }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ tx.note || tx.beneficiary || '—' }}</td>
                                <td class="px-4 py-3 text-right font-medium" :class="tx.type === 'income' ? 'text-green-600' : 'text-red-600'">
                                    {{ tx.type === 'income' ? '+' : '−' }} {{ fcfa(tx.amount) }}
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <Link :href="route('transactions.edit', tx.id)" class="text-blue-600 hover:underline mr-3">Éditer</Link>
                                    <button @click="remove(tx.id)" class="text-red-600 hover:underline">Suppr.</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="transactions.links.length > 3" class="flex flex-wrap gap-1 justify-center">
                    <Link
                        v-for="(link, i) in transactions.links"
                        :key="i"
                        :href="link.url || ''"
                        v-html="link.label"
                        :class="[
                            'rounded px-3 py-1 text-sm border',
                            link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50',
                            !link.url ? 'opacity-50 pointer-events-none' : ''
                        ]"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
