<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { fcfa } from '@/format';

defineProps<{
    budgets: Array<any>;
}>();

function remove(id: number) {
    if (!confirm('Supprimer cette enveloppe ?')) return;
    router.delete(route('budgets.destroy', id));
}
</script>

<template>
    <Head title="Budgets" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Enveloppes budgétaires</h2>
                <Link :href="route('budgets.create')" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                    + Nouvelle
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="budgets.length === 0" class="rounded-lg bg-white p-8 text-center shadow-sm">
                    <p class="text-gray-600">Aucune enveloppe définie.</p>
                    <Link :href="route('budgets.create')" class="mt-3 inline-block text-blue-600 hover:underline">
                        Créer ma première enveloppe →
                    </Link>
                </div>

                <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="b in budgets" :key="b.id" class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full" :style="{ backgroundColor: b.category?.color || '#94a3b8' }"></span>
                                <h3 class="font-semibold text-gray-800">{{ b.category?.name || 'Catégorie supprimée' }}</h3>
                            </div>
                            <span class="text-xs uppercase text-gray-500">{{ b.period === 'monthly' ? 'Mensuel' : 'Hebdo' }}</span>
                        </div>

                        <div class="mt-3 flex items-baseline justify-between text-sm">
                            <span class="text-gray-500">Dépensé</span>
                            <span class="font-medium">{{ fcfa(b.spent) }} / {{ fcfa(b.limit_amount) }}</span>
                        </div>

                        <div class="mt-2 h-2 w-full rounded-full bg-gray-100 overflow-hidden">
                            <div
                                class="h-full rounded-full transition-all"
                                :class="b.is_alerting ? 'bg-red-500' : 'bg-emerald-500'"
                                :style="{ width: Math.min(100, b.percent) + '%' }"
                            ></div>
                        </div>

                        <div class="mt-2 flex items-center justify-between text-xs">
                            <span :class="b.is_alerting ? 'text-red-600 font-medium' : 'text-gray-500'">
                                {{ b.percent }} % {{ b.is_alerting ? '⚠ alerte seuil ' + b.alert_threshold + '%' : '' }}
                            </span>
                            <span class="text-gray-500">Reste : {{ fcfa(b.remaining) }}</span>
                        </div>

                        <div class="mt-4 flex gap-3 text-sm">
                            <Link :href="route('budgets.edit', b.id)" class="text-blue-600 hover:underline">Éditer</Link>
                            <button @click="remove(b.id)" class="text-red-600 hover:underline">Supprimer</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
