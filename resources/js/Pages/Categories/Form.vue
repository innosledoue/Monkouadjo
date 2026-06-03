<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    category: {
        id: number;
        name: string;
        icon: string;
        color: string;
        type: string;
    } | null;
}>();

const isEdit = computed(() => !!props.category);

const form = useForm({
    name:  props.category?.name  ?? '',
    icon:  props.category?.icon  ?? 'wallet',
    color: props.category?.color ?? '#3b82f6',
    type:  props.category?.type  ?? 'expense',
});

const COLORS = [
    '#ef4444', '#f97316', '#f59e0b', '#eab308', '#84cc16',
    '#22c55e', '#10b981', '#14b8a6', '#06b6d4', '#3b82f6',
    '#6366f1', '#8b5cf6', '#a855f7', '#ec4899', '#f43f5e',
    '#64748b', '#78716c', '#94a3b8',
];

const ICONS = [
    'wallet', 'shopping-cart', 'utensils', 'car', 'home', 'zap',
    'droplets', 'phone', 'heart', 'graduation-cap', 'users', 'briefcase',
    'shirt', 'music', 'gamepad-2', 'plane', 'baby', 'gift',
    'building-2', 'landmark', 'coins', 'banknote', 'piggy-bank', 'circle-dot',
];

function submit() {
    if (isEdit.value) {
        form.put(route('categories.update', props.category!.id));
    } else {
        form.post(route('categories.store'));
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Modifier catégorie' : 'Nouvelle catégorie'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ isEdit ? 'Modifier' : 'Nouvelle' }} catégorie
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-5 rounded-lg bg-white p-6 shadow-sm">

                    <!-- Aperçu -->
                    <div class="flex items-center gap-3 rounded-md border border-gray-200 px-4 py-3 bg-gray-50">
                        <span class="inline-block h-5 w-5 rounded-full" :style="{ backgroundColor: form.color }"></span>
                        <span class="font-medium text-gray-800">{{ form.name || 'Nom de la catégorie' }}</span>
                        <span class="ml-auto text-xs text-gray-500">{{ form.type === 'expense' ? 'Dépense' : 'Revenu' }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input v-model="form.name" type="text" required maxlength="100"
                            class="w-full rounded-md border-gray-300" placeholder="Ex: Maquis & Sorties" />
                        <p v-if="form.errors.name" class="text-xs text-red-600 mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input v-model="form.type" type="radio" value="expense" class="text-red-500" />
                                <span class="text-sm">Dépense</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input v-model="form.type" type="radio" value="income" class="text-green-500" />
                                <span class="text-sm">Revenu</span>
                            </label>
                        </div>
                    </div>

                    <!-- Couleur -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Couleur *</label>
                        <div class="flex flex-wrap gap-2 mb-2">
                            <button
                                v-for="c in COLORS" :key="c" type="button"
                                @click="form.color = c"
                                class="h-7 w-7 rounded-full border-2 transition-transform hover:scale-110"
                                :class="form.color === c ? 'border-gray-800 scale-110' : 'border-transparent'"
                                :style="{ backgroundColor: c }"
                            />
                        </div>
                        <div class="flex items-center gap-2 mt-1">
                            <input v-model="form.color" type="color" class="h-8 w-14 rounded cursor-pointer border border-gray-300" />
                            <input v-model="form.color" type="text" maxlength="7" placeholder="#3b82f6"
                                class="w-28 rounded-md border-gray-300 text-sm font-mono" />
                        </div>
                        <p v-if="form.errors.color" class="text-xs text-red-600 mt-1">{{ form.errors.color }}</p>
                    </div>

                    <!-- Icône -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Icône (nom Lucide)</label>
                        <div class="flex flex-wrap gap-2 mb-2">
                            <button
                                v-for="ico in ICONS" :key="ico" type="button"
                                @click="form.icon = ico"
                                class="rounded-md border px-2 py-1 text-xs transition-colors"
                                :class="form.icon === ico ? 'border-indigo-500 bg-indigo-50 text-indigo-700 font-medium' : 'border-gray-200 text-gray-600 hover:bg-gray-50'"
                            >
                                {{ ico }}
                            </button>
                        </div>
                        <input v-model="form.icon" type="text" maxlength="64"
                            class="w-full rounded-md border-gray-300 text-sm font-mono" placeholder="ou saisir librement" />
                        <p v-if="form.errors.icon" class="text-xs text-red-600 mt-1">{{ form.errors.icon }}</p>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <Link :href="route('categories.index')" class="text-sm text-gray-600 hover:underline">← Annuler</Link>
                        <button type="submit" :disabled="form.processing"
                            class="rounded-md bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50">
                            {{ isEdit ? 'Mettre à jour' : 'Créer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
