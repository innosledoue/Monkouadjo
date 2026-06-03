<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { fcfa, fdate } from '@/format';
import { ref } from 'vue';

const props = defineProps<{
    tontines: Array<{
        id: number;
        name: string;
        members_count: number;
        contribution: string;
        frequency: 'weekly' | 'monthly';
        current_turn: number;
        start_date: string | null;
        status: 'active' | 'paused' | 'completed';
        note: string | null;
        cycle_total: number;
        progress_pct: number;
    }>;
    filters: { status?: string };
}>();

const filterForm = ref({ ...props.filters });

function applyFilters() {
    router.get(route('tontines.index'), filterForm.value, { preserveState: true, replace: true });
}

function reset() {
    filterForm.value = { status: '' };
    applyFilters();
}

function remove(id: number) {
    if (!confirm('Supprimer cette tontine ?')) return;
    router.delete(route('tontines.destroy', id));
}

const freqLabel = (f: string) => f === 'weekly' ? 'Hebdo' : 'Mensuel';
const statusLabel = (s: string) => ({ active: 'Active', paused: 'En pause', completed: 'Terminée' })[s] ?? s;
const statusBadge = (s: string) => ({
    active:    'bg-green-100 text-green-800',
    paused:    'bg-yellow-100 text-yellow-800',
    completed: 'bg-gray-100 text-gray-600',
})[s] ?? '';
</script>

<template>
    <Head title="Tontines" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Tontines</h2>
                <Link :href="route('tontines.create')" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                    + Nouvelle
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-5 sm:px-6 lg:px-8">

                <!-- Filtre statut -->
                <div class="rounded-lg bg-white p-4 shadow-sm flex flex-wrap gap-3">
                    <select v-model="filterForm.status" @change="applyFilters" class="rounded-md border-gray-300 text-sm">
                        <option value="">Tous statuts</option>
                        <option value="active">Active</option>
                        <option value="paused">En pause</option>
                        <option value="completed">Terminée</option>
                    </select>
                    <button @click="reset" class="rounded-md bg-gray-100 text-sm text-gray-700 hover:bg-gray-200 px-3">
                        Réinitialiser
                    </button>
                </div>

                <!-- Vide -->
                <div v-if="tontines.length === 0" class="rounded-lg bg-white p-8 shadow-sm text-center text-sm text-gray-500">
                    Aucune tontine enregistrée.
                </div>

                <!-- Cartes tontines -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div v-for="t in tontines" :key="t.id"
                        class="rounded-lg bg-white p-5 shadow-sm space-y-3"
                        :class="t.status === 'completed' ? 'opacity-70' : ''">

                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-800">{{ t.name }}</h3>
                                <p class="text-xs text-gray-500">{{ t.members_count }} membres · {{ freqLabel(t.frequency) }}</p>
                            </div>
                            <span :class="statusBadge(t.status)" class="inline-block rounded-full px-2 py-0.5 text-xs font-medium">
                                {{ statusLabel(t.status) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="text-gray-500">Cotisation</span>
                                <div class="font-medium text-gray-800">{{ fcfa(t.contribution) }}</div>
                            </div>
                            <div>
                                <span class="text-gray-500">Cagnotte / tour</span>
                                <div class="font-semibold text-teal-700">{{ fcfa(t.cycle_total) }}</div>
                            </div>
                        </div>

                        <!-- Progression tour -->
                        <div>
                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                <span>Tour {{ t.current_turn }} / {{ t.members_count }}</span>
                                <span>{{ t.progress_pct }} %</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all"
                                    :class="t.status === 'completed' ? 'bg-gray-400' : 'bg-teal-500'"
                                    :style="{ width: t.progress_pct + '%' }">
                                </div>
                            </div>
                        </div>

                        <div v-if="t.start_date" class="text-xs text-gray-500">
                            Démarrage : {{ fdate(t.start_date) }}
                        </div>

                        <div v-if="t.note" class="text-xs text-gray-500 italic">{{ t.note }}</div>

                        <div class="flex justify-end gap-3 pt-1 text-sm border-t border-gray-100">
                            <Link :href="route('tontines.edit', t.id)" class="text-blue-600 hover:underline">Éditer</Link>
                            <button @click="remove(t.id)" class="text-red-600 hover:underline">Supprimer</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
