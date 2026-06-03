<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { fcfa, fdate } from '@/format';
import { ref } from 'vue';

const props = defineProps<{
    debts: Array<{
        id: number;
        contact_name: string;
        contact_phone: string | null;
        type: 'lent' | 'borrowed';
        amount: string;
        due_date: string | null;
        status: 'pending' | 'partial' | 'settled';
        note: string | null;
    }>;
    summary: { lent_pending: number; borrowed_pending: number };
    filters: { type?: string; status?: string };
}>();

const filterForm = ref({ ...props.filters });

function applyFilters() {
    router.get(route('debts.index'), filterForm.value, { preserveState: true, replace: true });
}

function reset() {
    filterForm.value = { type: '', status: '' };
    applyFilters();
}

function remove(id: number) {
    if (!confirm('Supprimer cette dette ?')) return;
    router.delete(route('debts.destroy', id));
}

const typeLabel = (t: string) => t === 'lent' ? 'Prêté' : 'Emprunté';
const statusLabel = (s: string) => ({ pending: 'En attente', partial: 'Partiel', settled: 'Soldé' })[s] ?? s;
const statusColor = (s: string) => ({ pending: 'text-orange-600', partial: 'text-blue-600', settled: 'text-green-600' })[s] ?? '';
</script>

<template>
    <Head title="Dettes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Dettes & Prêts</h2>
                <Link :href="route('debts.create')" class="rounded-md bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700">
                    + Nouvelle
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-5 sm:px-6 lg:px-8">

                <!-- Résumé -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-lg bg-white p-5 shadow-sm border-l-4 border-green-500">
                        <div class="text-sm text-gray-500">À recevoir (prêts actifs)</div>
                        <div class="mt-1 text-2xl font-semibold text-green-700">{{ fcfa(summary.lent_pending) }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow-sm border-l-4 border-red-500">
                        <div class="text-sm text-gray-500">À rembourser (dettes actives)</div>
                        <div class="mt-1 text-2xl font-semibold text-red-700">{{ fcfa(summary.borrowed_pending) }}</div>
                    </div>
                </div>

                <!-- Filtres -->
                <div class="rounded-lg bg-white p-4 shadow-sm grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <select v-model="filterForm.type" @change="applyFilters" class="rounded-md border-gray-300 text-sm">
                        <option value="">Tous types</option>
                        <option value="lent">Prêté</option>
                        <option value="borrowed">Emprunté</option>
                    </select>
                    <select v-model="filterForm.status" @change="applyFilters" class="rounded-md border-gray-300 text-sm">
                        <option value="">Tous statuts</option>
                        <option value="pending">En attente</option>
                        <option value="partial">Partiel</option>
                        <option value="settled">Soldé</option>
                    </select>
                    <button @click="reset" class="col-span-2 sm:col-span-1 rounded-md bg-gray-100 text-sm text-gray-700 hover:bg-gray-200 px-3">
                        Réinitialiser
                    </button>
                </div>

                <!-- Liste -->
                <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-medium uppercase text-gray-500">
                                <th class="px-4 py-3">Contact</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Statut</th>
                                <th class="px-4 py-3">Échéance</th>
                                <th class="px-4 py-3">Note</th>
                                <th class="px-4 py-3 text-right">Montant</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="debts.length === 0">
                                <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                                    Aucune dette enregistrée.
                                </td>
                            </tr>
                            <tr v-for="d in debts" :key="d.id" class="text-sm" :class="d.status === 'settled' ? 'opacity-60' : ''">
                                <td class="px-4 py-3 font-medium text-gray-800">
                                    {{ d.contact_name }}
                                    <span v-if="d.contact_phone" class="block text-xs text-gray-400 font-normal">{{ d.contact_phone }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span :class="d.type === 'lent' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                        class="inline-block rounded-full px-2 py-0.5 text-xs font-medium">
                                        {{ typeLabel(d.type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-medium" :class="statusColor(d.status)">{{ statusLabel(d.status) }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ d.due_date ? fdate(d.due_date) : '—' }}</td>
                                <td class="px-4 py-3 text-gray-500 max-w-xs truncate">{{ d.note || '—' }}</td>
                                <td class="px-4 py-3 text-right font-semibold" :class="d.type === 'lent' ? 'text-green-700' : 'text-red-700'">
                                    {{ fcfa(d.amount) }}
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <Link :href="route('debts.edit', d.id)" class="text-blue-600 hover:underline mr-3">Éditer</Link>
                                    <button @click="remove(d.id)" class="text-red-600 hover:underline">Suppr.</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
