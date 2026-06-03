<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { fcfa } from '@/format';
import { computed, ref } from 'vue';

const props = defineProps<{
    summary: {
        income: number;
        expense: number;
        net: number;
        savings_rate: number;
        period_label: string;
    };
    monthly: Array<{ label: string; income: number; expense: number }>;
    by_category: Array<{ category_id: number; name: string; color: string; total: number; pct: number }>;
    filters: { period?: string; from?: string; to?: string };
}>();

const period = ref(props.filters.period ?? 'current_month');
const customFrom = ref(props.filters.from ?? '');
const customTo   = ref(props.filters.to   ?? '');

const PERIODS = [
    { value: 'current_month',  label: 'Ce mois' },
    { value: 'last_3_months',  label: '3 derniers mois' },
    { value: 'last_6_months',  label: '6 derniers mois' },
    { value: 'current_year',   label: 'Cette année' },
    { value: 'custom',         label: 'Personnalisé' },
];

function applyPeriod() {
    const params: Record<string, string> = { period: period.value };
    if (period.value === 'custom') {
        if (customFrom.value) params.from = customFrom.value;
        if (customTo.value)   params.to   = customTo.value;
    }
    router.get(route('reports.index'), params, { preserveState: true, replace: true });
}

function exportCsv() {
    const params = new URLSearchParams({ period: period.value });
    if (period.value === 'custom') {
        if (customFrom.value) params.set('from', customFrom.value);
        if (customTo.value)   params.set('to', customTo.value);
    }
    window.location.href = route('reports.export') + '?' + params.toString();
}

// Graphique mensuel — hauteur relative par rapport au max
const monthlyMax = computed(() => {
    return Math.max(...props.monthly.map(m => Math.max(m.income, m.expense)), 1);
});
function barPct(val: number) {
    return Math.round((val / monthlyMax.value) * 100);
}

const savingsColor = computed(() =>
    props.summary.savings_rate >= 20 ? 'text-green-600'
    : props.summary.savings_rate >= 0 ? 'text-amber-600'
    : 'text-red-600'
);
</script>

<template>
    <Head title="Rapports" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between flex-wrap gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Rapports — {{ summary.period_label }}
                </h2>
                <button @click="exportCsv"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    ↓ Export CSV
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-7 sm:px-6 lg:px-8">

                <!-- Sélecteur de période -->
                <div class="rounded-lg bg-white p-4 shadow-sm flex flex-wrap gap-2 items-center">
                    <button v-for="p in PERIODS" :key="p.value"
                        @click="period = p.value; if (p.value !== 'custom') applyPeriod()"
                        :class="[
                            'rounded-full px-4 py-1.5 text-sm font-medium transition-colors',
                            period === p.value
                                ? 'bg-blue-600 text-white'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                        ]">
                        {{ p.label }}
                    </button>

                    <!-- Plage personnalisée -->
                    <template v-if="period === 'custom'">
                        <input v-model="customFrom" type="date" class="rounded-md border-gray-300 text-sm" />
                        <span class="text-gray-400 text-sm">→</span>
                        <input v-model="customTo" type="date" class="rounded-md border-gray-300 text-sm" />
                        <button @click="applyPeriod"
                            class="rounded-md bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700">
                            Appliquer
                        </button>
                    </template>
                </div>

                <!-- Cartes résumé -->
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="text-xs text-gray-500 uppercase tracking-wide">Revenus</div>
                        <div class="mt-2 text-2xl font-bold text-green-600">{{ fcfa(summary.income) }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="text-xs text-gray-500 uppercase tracking-wide">Dépenses</div>
                        <div class="mt-2 text-2xl font-bold text-red-600">{{ fcfa(summary.expense) }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="text-xs text-gray-500 uppercase tracking-wide">Solde net</div>
                        <div class="mt-2 text-2xl font-bold" :class="summary.net >= 0 ? 'text-blue-600' : 'text-red-600'">
                            {{ fcfa(summary.net) }}
                        </div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="text-xs text-gray-500 uppercase tracking-wide">Taux d'épargne</div>
                        <div class="mt-2 text-2xl font-bold" :class="savingsColor">
                            {{ summary.savings_rate }} %
                        </div>
                        <div class="mt-1 text-xs text-gray-400">
                            {{ summary.savings_rate >= 20 ? 'Excellent' : summary.savings_rate >= 10 ? 'Bien' : summary.savings_rate >= 0 ? 'Attention' : 'Déficit' }}
                        </div>
                    </div>
                </div>

                <!-- Évolution mensuelle (12 mois) -->
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="mb-5 font-semibold text-gray-800">Évolution mensuelle (12 mois)</h3>

                    <div class="flex items-end gap-1.5 h-48 overflow-x-auto pb-1">
                        <div v-for="m in monthly" :key="m.label"
                            class="flex flex-col items-center gap-1 min-w-[52px] flex-1">
                            <!-- Barres -->
                            <div class="w-full flex items-end justify-center gap-1" style="height: 160px;">
                                <div class="flex flex-col items-center justify-end" style="height: 160px; width: 44%;">
                                    <div class="w-full rounded-t transition-all bg-green-400"
                                        :style="{ height: barPct(m.income) + '%', minHeight: m.income > 0 ? '4px' : '0' }"
                                        :title="'Revenus : ' + m.income">
                                    </div>
                                </div>
                                <div class="flex flex-col items-center justify-end" style="height: 160px; width: 44%;">
                                    <div class="w-full rounded-t transition-all bg-red-400"
                                        :style="{ height: barPct(m.expense) + '%', minHeight: m.expense > 0 ? '4px' : '0' }"
                                        :title="'Dépenses : ' + m.expense">
                                    </div>
                                </div>
                            </div>
                            <!-- Label mois -->
                            <div class="text-xs text-gray-500 whitespace-nowrap capitalize">{{ m.label }}</div>
                        </div>
                    </div>

                    <!-- Légende -->
                    <div class="mt-3 flex gap-5 text-xs text-gray-500">
                        <span class="flex items-center gap-1.5">
                            <span class="inline-block h-3 w-3 rounded-sm bg-green-400"></span> Revenus
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="inline-block h-3 w-3 rounded-sm bg-red-400"></span> Dépenses
                        </span>
                    </div>
                </div>

                <!-- Répartition par catégorie -->
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="mb-5 font-semibold text-gray-800">Répartition des dépenses par catégorie</h3>

                    <div v-if="by_category.length === 0" class="text-sm text-gray-500">
                        Aucune dépense sur cette période.
                    </div>

                    <ul v-else class="space-y-4">
                        <li v-for="c in by_category" :key="c.category_id">
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="flex items-center gap-2 font-medium text-gray-800">
                                    <span class="h-3 w-3 rounded-full inline-block" :style="{ backgroundColor: c.color }"></span>
                                    {{ c.name }}
                                </span>
                                <span class="text-gray-500">
                                    {{ fcfa(c.total) }}
                                    <span class="ml-2 text-xs text-gray-400">{{ c.pct }} %</span>
                                </span>
                            </div>
                            <!-- Barre de progression -->
                            <div class="h-2.5 w-full rounded-full bg-gray-100">
                                <div class="h-2.5 rounded-full transition-all"
                                    :style="{ width: c.pct + '%', backgroundColor: c.color }">
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
