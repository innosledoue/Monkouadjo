<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    debt: {
        id: number;
        contact_name: string;
        contact_phone: string | null;
        type: string;
        amount: string;
        due_date: string | null;
        status: string;
        note: string | null;
    } | null;
}>();

const isEdit = computed(() => !!props.debt);

const form = useForm({
    contact_name:  props.debt?.contact_name  ?? '',
    contact_phone: props.debt?.contact_phone ?? '',
    type:          props.debt?.type          ?? 'borrowed',
    amount:        props.debt?.amount        ?? '',
    due_date:      props.debt?.due_date      ?? '',
    status:        props.debt?.status        ?? 'pending',
    note:          props.debt?.note          ?? '',
});

function submit() {
    if (isEdit.value) {
        form.put(route('debts.update', props.debt!.id));
    } else {
        form.post(route('debts.store'));
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Modifier dette' : 'Nouvelle dette'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ isEdit ? 'Modifier' : 'Nouvelle' }} dette / prêt
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-5 rounded-lg bg-white p-6 shadow-sm">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom du contact *</label>
                            <input v-model="form.contact_name" type="text" required maxlength="255"
                                class="w-full rounded-md border-gray-300" placeholder="Ex: Kouamé Yao" />
                            <p v-if="form.errors.contact_name" class="text-xs text-red-600 mt-1">{{ form.errors.contact_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                            <input v-model="form.contact_phone" type="text" maxlength="30"
                                class="w-full rounded-md border-gray-300" placeholder="+225 07 00 00 00 00" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input v-model="form.type" type="radio" value="borrowed" class="text-red-600" />
                                <span class="text-sm">J'ai emprunté <span class="text-gray-500">(je dois rembourser)</span></span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input v-model="form.type" type="radio" value="lent" class="text-green-600" />
                                <span class="text-sm">J'ai prêté <span class="text-gray-500">(on me doit)</span></span>
                            </label>
                        </div>
                        <p v-if="form.errors.type" class="text-xs text-red-600 mt-1">{{ form.errors.type }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Montant (FCFA) *</label>
                            <input v-model="form.amount" type="number" min="1" required
                                class="w-full rounded-md border-gray-300 text-lg" placeholder="50000" />
                            <p v-if="form.errors.amount" class="text-xs text-red-600 mt-1">{{ form.errors.amount }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date d'échéance</label>
                            <input v-model="form.due_date" type="date" class="w-full rounded-md border-gray-300" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Statut *</label>
                        <select v-model="form.status" required class="w-full rounded-md border-gray-300">
                            <option value="pending">En attente</option>
                            <option value="partial">Remboursement partiel</option>
                            <option value="settled">Soldé</option>
                        </select>
                        <p v-if="form.errors.status" class="text-xs text-red-600 mt-1">{{ form.errors.status }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                        <textarea v-model="form.note" rows="3" maxlength="500"
                            class="w-full rounded-md border-gray-300 text-sm" placeholder="Raison, conditions..."></textarea>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <Link :href="route('debts.index')" class="text-sm text-gray-600 hover:underline">← Annuler</Link>
                        <button type="submit" :disabled="form.processing"
                            class="rounded-md bg-purple-600 px-5 py-2 text-sm font-medium text-white hover:bg-purple-700 disabled:opacity-50">
                            {{ isEdit ? 'Mettre à jour' : 'Enregistrer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
