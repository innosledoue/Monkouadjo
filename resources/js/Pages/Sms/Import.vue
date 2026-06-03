<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { fcfa, fdatetime, paymentLabel } from '@/format';

interface ParsedSms {
    provider: string;
    type: 'expense' | 'income';
    amount: number | null;
    fees: number | null;
    balance: number | null;
    beneficiary: string | null;
    transaction_id: string | null;
    occurred_at: string | null;
    payment_method: string;
    note: string;
    raw: string;
    confidence: number;
    _selected?: boolean;
}

const smsText = ref('');
const items = ref<ParsedSms[]>([]);
const loading = ref(false);
const importing = ref(false);

const csrf = computed(() => (document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content) ?? '');

async function preview() {
    if (!smsText.value.trim()) return;
    loading.value = true;
    try {
        const res = await fetch('/sms/preview', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf.value,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ sms: smsText.value }),
        });
        const data = await res.json();
        items.value = (data.items || []).map((i: ParsedSms) => ({ ...i, _selected: !!i.amount }));
    } finally {
        loading.value = false;
    }
}

const selectedCount = computed(() => items.value.filter(i => i._selected).length);

async function importSelected() {
    const selected = items.value.filter(i => i._selected && i.amount);
    if (selected.length === 0) return;
    importing.value = true;
    router.post('/sms/store', {
        items: selected.map(s => ({
            amount: s.amount,
            type: s.type,
            payment_method: s.payment_method,
            occurred_at: s.occurred_at,
            beneficiary: s.beneficiary,
            note: s.note,
            category_id: null,
        })),
    }, {
        onFinish: () => { importing.value = false; },
    });
}

const sample = `Vous avez reçu 25 000 FCFA de KOUAME YAO. Frais: 0 F. Nouveau solde: 47 320 FCFA. Trans: WV2026060311080012. le 03/06/2026 à 11:08 - Wave

Orange Money: Paiement de 5 500 FCFA à MAQUIS LE BARON effectué. Frais: 50 F. Solde: 12 800 FCFA. Ref: OM9981234567. 03/06/2026 12:30

MTN MoMo: Transfert de 10 000 FCFA envoyé à MARIE BAMBA (+22507070707). Frais 100 F. Solde 35 200 FCFA. Trans MM77881234. 03/06/2026 13:45`;
</script>

<template>
    <Head title="Import SMS Mobile Money" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Import SMS Mobile Money (OM / Wave / MoMo)
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl space-y-5 sm:px-6 lg:px-8">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-600 mb-3">
                        Collez ici un ou plusieurs SMS reçus de votre opérateur. Séparez les messages par <strong>une ligne vide</strong> ou <strong>---</strong>.
                        Le parser extrait automatiquement montant, frais, solde, bénéficiaire et référence.
                    </p>
                    <textarea
                        v-model="smsText"
                        rows="8"
                        class="w-full rounded-md border-gray-300 text-sm font-mono"
                        placeholder="Collez vos SMS ici…"
                    ></textarea>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button @click="preview" :disabled="loading || !smsText.trim()"
                            class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                            {{ loading ? 'Analyse…' : 'Analyser' }}
                        </button>
                        <button @click="smsText = sample" class="rounded-md bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200">
                            Charger des exemples
                        </button>
                    </div>
                </div>

                <div v-if="items.length > 0" class="rounded-lg bg-white shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between border-b border-gray-100 p-4">
                        <span class="text-sm text-gray-700"><strong>{{ items.length }}</strong> message(s) analysé(s) — <strong>{{ selectedCount }}</strong> sélectionné(s)</span>
                        <button @click="importSelected" :disabled="selectedCount === 0 || importing"
                            class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">
                            {{ importing ? 'Import…' : `Importer ${selectedCount}` }}
                        </button>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        <li v-for="(it, i) in items" :key="i" class="p-4">
                            <label class="flex gap-3 items-start cursor-pointer">
                                <input type="checkbox" v-model="it._selected" :disabled="!it.amount" class="mt-1 rounded border-gray-300" />
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 text-sm">
                                        <span class="rounded px-2 py-0.5 text-xs font-medium uppercase"
                                            :class="{
                                                'bg-orange-100 text-orange-700': it.provider === 'om',
                                                'bg-blue-100 text-blue-700': it.provider === 'wave',
                                                'bg-yellow-100 text-yellow-800': it.provider === 'momo',
                                                'bg-gray-100 text-gray-600': it.provider === 'unknown',
                                            }">
                                            {{ it.provider }}
                                        </span>
                                        <span class="rounded px-2 py-0.5 text-xs"
                                            :class="it.type === 'income' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                                            {{ it.type === 'income' ? 'Reçu' : 'Envoyé' }}
                                        </span>
                                        <span class="text-base font-semibold" :class="it.type === 'income' ? 'text-green-700' : 'text-red-700'">
                                            {{ it.amount ? fcfa(it.amount) : '— montant non détecté —' }}
                                        </span>
                                        <span class="text-xs text-gray-500">conf. {{ Math.round(it.confidence * 100) }}%</span>
                                    </div>
                                    <div class="mt-1 text-xs text-gray-600 space-x-3">
                                        <span v-if="it.beneficiary">à {{ it.beneficiary }}</span>
                                        <span v-if="it.fees">frais {{ fcfa(it.fees) }}</span>
                                        <span v-if="it.balance !== null">solde {{ fcfa(it.balance) }}</span>
                                        <span v-if="it.occurred_at">{{ fdatetime(it.occurred_at) }}</span>
                                        <span v-if="it.transaction_id">ref {{ it.transaction_id }}</span>
                                    </div>
                                    <pre class="mt-2 text-xs text-gray-500 whitespace-pre-wrap break-words font-mono">{{ it.raw }}</pre>
                                </div>
                            </label>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
