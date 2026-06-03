<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    budget: any | null;
    categories: Array<{ id: number; name: string; color: string }>;
}>();

const isEdit = computed(() => !!props.budget);

const form = useForm({
    category_id: props.budget?.category_id ?? null,
    limit_amount: props.budget?.limit_amount ?? '',
    alert_threshold: props.budget?.alert_threshold ?? 80,
    period: props.budget?.period ?? 'monthly',
    is_active: props.budget?.is_active ?? true,
});

function submit() {
    if (isEdit.value) {
        form.put(route('budgets.update', props.budget.id));
    } else {
        form.post(route('budgets.store'));
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Modifier enveloppe' : 'Nouvelle enveloppe'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ isEdit ? 'Modifier' : 'Nouvelle' }} enveloppe budgétaire
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                        <select v-model="form.category_id" required class="w-full rounded-md border-gray-300">
                            <option :value="null" disabled>— Choisir —</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                        <p v-if="form.errors.category_id" class="text-xs text-red-600 mt-1">{{ form.errors.category_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Limite (FCFA)</label>
                        <input v-model="form.limit_amount" type="number" min="1" required
                            class="w-full rounded-md border-gray-300 text-lg" placeholder="50000" />
                        <p v-if="form.errors.limit_amount" class="text-xs text-red-600 mt-1">{{ form.errors.limit_amount }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Période</label>
                        <select v-model="form.period" class="w-full rounded-md border-gray-300">
                            <option value="monthly">Mensuel</option>
                            <option value="weekly">Hebdomadaire</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Seuil d'alerte : {{ form.alert_threshold }} %
                        </label>
                        <input v-model.number="form.alert_threshold" type="range" min="50" max="100" step="5"
                            class="w-full" />
                        <p class="text-xs text-gray-500 mt-1">Une alerte s'affichera quand vous atteindrez ce pourcentage de la limite.</p>
                    </div>

                    <label class="flex items-center gap-2 text-sm">
                        <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300" />
                        Enveloppe active
                    </label>

                    <div class="flex items-center justify-between pt-2">
                        <Link :href="route('budgets.index')" class="text-sm text-gray-600 hover:underline">← Annuler</Link>
                        <button type="submit" :disabled="form.processing"
                            class="rounded-md bg-emerald-600 px-5 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">
                            {{ isEdit ? 'Mettre à jour' : 'Créer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
