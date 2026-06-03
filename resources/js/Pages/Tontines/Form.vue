<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    tontine: {
        id: number;
        name: string;
        members_count: number;
        contribution: string;
        frequency: string;
        current_turn: number;
        start_date: string | null;
        status: string;
        note: string | null;
    } | null;
}>();

const isEdit = computed(() => !!props.tontine);

const form = useForm({
    name:          props.tontine?.name          ?? '',
    members_count: props.tontine?.members_count ?? 10,
    contribution:  props.tontine?.contribution  ?? '',
    frequency:     props.tontine?.frequency     ?? 'monthly',
    current_turn:  props.tontine?.current_turn  ?? 1,
    start_date:    props.tontine?.start_date    ?? '',
    status:        props.tontine?.status        ?? 'active',
    note:          props.tontine?.note          ?? '',
});

const cycleTotal = computed(() => {
    const c = parseFloat(String(form.contribution)) || 0;
    return c * form.members_count;
});

function submit() {
    if (isEdit.value) {
        form.put(route('tontines.update', props.tontine!.id));
    } else {
        form.post(route('tontines.store'));
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Modifier tontine' : 'Nouvelle tontine'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ isEdit ? 'Modifier' : 'Nouvelle' }} tontine
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-5 rounded-lg bg-white p-6 shadow-sm">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom de la tontine *</label>
                        <input v-model="form.name" type="text" required maxlength="255"
                            class="w-full rounded-md border-gray-300" placeholder="Ex: Tontine du quartier" />
                        <p v-if="form.errors.name" class="text-xs text-red-600 mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de membres *</label>
                            <input v-model.number="form.members_count" type="number" min="2" max="200" required
                                class="w-full rounded-md border-gray-300" />
                            <p v-if="form.errors.members_count" class="text-xs text-red-600 mt-1">{{ form.errors.members_count }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cotisation / tour (FCFA) *</label>
                            <input v-model="form.contribution" type="number" min="1" required
                                class="w-full rounded-md border-gray-300" placeholder="20000" />
                            <p v-if="form.errors.contribution" class="text-xs text-red-600 mt-1">{{ form.errors.contribution }}</p>
                        </div>
                    </div>

                    <!-- Aperçu cagnotte -->
                    <div v-if="cycleTotal > 0" class="rounded-md bg-teal-50 border border-teal-200 px-4 py-3 text-sm text-teal-800">
                        Cagnotte par tour : <strong>{{ new Intl.NumberFormat('fr-FR').format(cycleTotal) }} FCFA</strong>
                        <span class="text-teal-600"> ({{ form.members_count }} membres × {{ form.contribution }} FCFA)</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fréquence *</label>
                            <select v-model="form.frequency" required class="w-full rounded-md border-gray-300">
                                <option value="monthly">Mensuelle</option>
                                <option value="weekly">Hebdomadaire</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tour actuel *</label>
                            <input v-model.number="form.current_turn" type="number" min="1" :max="form.members_count" required
                                class="w-full rounded-md border-gray-300" />
                            <p v-if="form.errors.current_turn" class="text-xs text-red-600 mt-1">{{ form.errors.current_turn }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date de démarrage</label>
                            <input v-model="form.start_date" type="date" class="w-full rounded-md border-gray-300" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Statut *</label>
                            <select v-model="form.status" required class="w-full rounded-md border-gray-300">
                                <option value="active">Active</option>
                                <option value="paused">En pause</option>
                                <option value="completed">Terminée</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                        <textarea v-model="form.note" rows="3" maxlength="500"
                            class="w-full rounded-md border-gray-300 text-sm" placeholder="Informations complémentaires..."></textarea>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <Link :href="route('tontines.index')" class="text-sm text-gray-600 hover:underline">← Annuler</Link>
                        <button type="submit" :disabled="form.processing"
                            class="rounded-md bg-teal-600 px-5 py-2 text-sm font-medium text-white hover:bg-teal-700 disabled:opacity-50">
                            {{ isEdit ? 'Mettre à jour' : 'Créer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
