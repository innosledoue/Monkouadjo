<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { fcfa, fdate, fdatetime, paymentLabel } from '@/format';

defineProps<{
    stats: {
        income: number;
        expense: number;
        balance: number;
        vital_balance: number;
        period_label: string;
    };
    by_category: Array<{ category_id: number; name: string; icon: string; color: string; total: number }>;
    recent_transactions: Array<any>;
    pending_debts: Array<{ id: number; contact_name: string; type: string; amount: string; due_date: string | null; status: string }>;
    debts_summary: { lent: number; borrowed: number };
    active_tontines: Array<{ id: number; name: string; contribution: string; members_count: number; current_turn: number; frequency: string }>;
}>();
</script>

<template>
    <Head title="Tableau de bord" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 capitalize">
                Tableau de bord — {{ stats.period_label }}
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <!-- Cartes synthétiques -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="text-sm text-gray-500">Revenus du mois</div>
                        <div class="mt-2 text-2xl font-semibold text-green-600">{{ fcfa(stats.income) }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="text-sm text-gray-500">Dépenses du mois</div>
                        <div class="mt-2 text-2xl font-semibold text-red-600">{{ fcfa(stats.expense) }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="text-sm text-gray-500">Solde du mois</div>
                        <div class="mt-2 text-2xl font-semibold" :class="stats.balance >= 0 ? 'text-blue-600' : 'text-red-600'">
                            {{ fcfa(stats.balance) }}
                        </div>
                    </div>
                    <div class="rounded-lg bg-amber-50 border border-amber-200 p-5 shadow-sm">
                        <div class="text-sm text-amber-800">Solde vital (Mode Urgence)</div>
                        <div class="mt-2 text-2xl font-semibold" :class="stats.vital_balance >= 0 ? 'text-amber-700' : 'text-red-700'">
                            {{ fcfa(stats.vital_balance) }}
                        </div>
                        <div class="mt-1 text-xs text-amber-700">Après charges fixes restantes</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Dépenses par catégorie -->
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h3 class="mb-4 font-semibold text-gray-800">Dépenses par catégorie</h3>
                        <div v-if="by_category.length === 0" class="text-sm text-gray-500">
                            Aucune dépense ce mois-ci.
                        </div>
                        <ul v-else class="space-y-3">
                            <li v-for="row in by_category" :key="row.category_id" class="flex items-center gap-3">
                                <span class="inline-block h-3 w-3 rounded-full" :style="{ backgroundColor: row.color }"></span>
                                <span class="flex-1 text-sm text-gray-700">{{ row.name }}</span>
                                <span class="text-sm font-medium text-gray-900">{{ fcfa(row.total) }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Transactions récentes -->
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-800">Transactions récentes</h3>
                            <Link :href="route('transactions.index')" class="text-sm text-blue-600 hover:underline">
                                Voir tout →
                            </Link>
                        </div>
                        <div v-if="recent_transactions.length === 0" class="text-sm text-gray-500">
                            Aucune transaction. <Link :href="route('transactions.create')" class="text-blue-600 hover:underline">Ajouter</Link>
                        </div>
                        <ul v-else class="divide-y divide-gray-100">
                            <li v-for="tx in recent_transactions" :key="tx.id" class="py-2.5 flex items-center gap-3">
                                <span class="inline-block h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: tx.category?.color || '#94a3b8' }"></span>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm text-gray-800 truncate">{{ tx.category?.name || 'Sans catégorie' }} · {{ paymentLabel(tx.payment_method) }}</div>
                                    <div class="text-xs text-gray-500">{{ fdatetime(tx.occurred_at) }}<span v-if="tx.note"> — {{ tx.note }}</span></div>
                                </div>
                                <span class="text-sm font-medium" :class="tx.type === 'income' ? 'text-green-600' : 'text-red-600'">
                                    {{ tx.type === 'income' ? '+' : '−' }} {{ fcfa(tx.amount) }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Dettes & Tontines -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Dettes en attente -->
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-800">Dettes & Prêts actifs</h3>
                            <Link :href="route('debts.index')" class="text-sm text-blue-600 hover:underline">Voir tout →</Link>
                        </div>
                        <div class="mb-3 grid grid-cols-2 gap-2 text-sm">
                            <div class="rounded-md bg-green-50 px-3 py-2">
                                <div class="text-xs text-gray-500">À recevoir</div>
                                <div class="font-semibold text-green-700">{{ fcfa(debts_summary.lent) }}</div>
                            </div>
                            <div class="rounded-md bg-red-50 px-3 py-2">
                                <div class="text-xs text-gray-500">À rembourser</div>
                                <div class="font-semibold text-red-700">{{ fcfa(debts_summary.borrowed) }}</div>
                            </div>
                        </div>
                        <div v-if="pending_debts.length === 0" class="text-sm text-gray-500">
                            Aucune dette en attente.
                            <Link :href="route('debts.create')" class="text-blue-600 hover:underline">Ajouter</Link>
                        </div>
                        <ul v-else class="divide-y divide-gray-100">
                            <li v-for="d in pending_debts" :key="d.id" class="py-2 flex items-center justify-between text-sm">
                                <div>
                                    <span class="font-medium text-gray-800">{{ d.contact_name }}</span>
                                    <span :class="d.type === 'lent' ? 'text-green-600' : 'text-red-600'" class="ml-2 text-xs">
                                        {{ d.type === 'lent' ? 'prêté' : 'emprunté' }}
                                    </span>
                                    <span v-if="d.due_date" class="ml-2 text-xs text-gray-400">· {{ fdate(d.due_date) }}</span>
                                </div>
                                <span class="font-medium" :class="d.type === 'lent' ? 'text-green-700' : 'text-red-700'">
                                    {{ fcfa(d.amount) }}
                                </span>
                            </li>
                        </ul>
                    </div>

                    <!-- Tontines actives -->
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-800">Tontines actives</h3>
                            <Link :href="route('tontines.index')" class="text-sm text-blue-600 hover:underline">Voir tout →</Link>
                        </div>
                        <div v-if="active_tontines.length === 0" class="text-sm text-gray-500">
                            Aucune tontine active.
                            <Link :href="route('tontines.create')" class="text-blue-600 hover:underline">Ajouter</Link>
                        </div>
                        <ul v-else class="divide-y divide-gray-100">
                            <li v-for="t in active_tontines" :key="t.id" class="py-2.5 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-800">{{ t.name }}</span>
                                    <span class="font-semibold text-teal-700">{{ fcfa(t.contribution) }}</span>
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    Tour {{ t.current_turn }} / {{ t.members_count }} ·
                                    {{ t.frequency === 'monthly' ? 'Mensuel' : 'Hebdo' }} ·
                                    Cagnotte : {{ fcfa(parseFloat(t.contribution) * t.members_count) }}
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <Link :href="route('transactions.create')" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        + Nouvelle transaction
                    </Link>
                    <Link :href="route('budgets.create')" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                        + Nouvelle enveloppe budget
                    </Link>
                    <Link :href="route('debts.create')" class="rounded-md bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700">
                        + Nouvelle dette
                    </Link>
                    <Link :href="route('tontines.create')" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                        + Nouvelle tontine
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
