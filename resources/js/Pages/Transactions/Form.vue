<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { PAYMENT_METHODS } from '@/format';
import { computed, ref, watch } from 'vue';
import { enqueueOffline } from '@/offline';
import { useVoiceInput } from '@/composables/useVoiceInput';

const { transcript, listening, error: voiceError, isSupported: voiceSupported, start: startVoice, stop: stopVoice } = useVoiceInput('fr-FR');
const parsingNlp = ref(false);
const nlpHint = ref<string | null>(null);

const csrfToken = () => document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '';

async function applyNlpFromText(text: string) {
    if (!text.trim()) return;
    parsingNlp.value = true;
    nlpHint.value = null;
    try {
        const res = await fetch('/ai/parse-text', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ text }),
        });
        if (!res.ok) throw new Error('NLP HTTP ' + res.status);
        const data = await res.json();
        if (data.amount) form.amount = data.amount;
        if (data.type) form.type = data.type;
        if (data.category_id) form.category_id = data.category_id;
        if (data.payment_method) form.payment_method = data.payment_method;
        if (data.beneficiary) form.beneficiary = data.beneficiary;
        if (data.note) form.note = data.note;
        form.source = 'voice';
        nlpHint.value = `IA (${data.driver}) — confiance ${Math.round((data.confidence ?? 0) * 100)}%${data.category_name ? ' · ' + data.category_name : ''}`;
    } catch (e: any) {
        nlpHint.value = 'Erreur NLP : ' + (e?.message || 'inconnue');
    } finally {
        parsingNlp.value = false;
    }
}

watch(listening, (now, prev) => {
    if (prev && !now && transcript.value) {
        applyNlpFromText(transcript.value);
    }
});

const props = defineProps<{
    transaction: any | null;
    categories: Array<{ id: number; name: string; type: string; color: string }>;
}>();

const isEdit = computed(() => !!props.transaction);

const form = useForm({
    type: props.transaction?.type ?? 'expense',
    amount: props.transaction?.amount ?? '',
    category_id: props.transaction?.category_id ?? null,
    occurred_at: props.transaction?.occurred_at?.slice(0, 16) ?? new Date().toISOString().slice(0, 16),
    payment_method: props.transaction?.payment_method ?? 'cash',
    beneficiary: props.transaction?.beneficiary ?? '',
    note: props.transaction?.note ?? '',
    source: props.transaction?.source ?? 'manual',
});

const filteredCategories = computed(() =>
    props.categories.filter(c => c.type === form.type)
);

async function submit() {
    if (!navigator.onLine && !isEdit.value) {
        await enqueueOffline('transactions', form.data());
        alert('Hors ligne : transaction mise en file d\'attente locale (IndexedDB). Elle sera envoyée au prochain retour réseau.');
        form.reset();
        return;
    }
    if (isEdit.value) {
        form.put(route('transactions.update', props.transaction.id));
    } else {
        form.post(route('transactions.store'));
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Modifier transaction' : 'Nouvelle transaction'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ isEdit ? 'Modifier' : 'Nouvelle' }} transaction
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <!-- Bloc saisie intelligente (IA locale) -->
                <div v-if="!isEdit" class="mb-4 rounded-lg border border-blue-200 bg-blue-50 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-blue-900">🎙 Saisie intelligente</span>
                        <span class="text-xs text-blue-700">Web Speech API + NLP local</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button v-if="voiceSupported" type="button"
                            @click="listening ? stopVoice() : startVoice()"
                            :class="['rounded-full px-4 py-2 text-sm font-medium border transition',
                                listening ? 'bg-red-600 text-white border-red-600 animate-pulse' : 'bg-white text-blue-700 border-blue-300 hover:bg-blue-100']">
                            {{ listening ? '⏹ Arrêter' : '🎤 Dicter' }}
                        </button>
                        <input v-model="transcript" @keyup.enter="applyNlpFromText(transcript)" type="text"
                            placeholder="Ex : J'ai bouffé 2000 au maquis"
                            class="flex-1 min-w-[200px] rounded-md border-blue-300 text-sm" />
                        <button type="button" @click="applyNlpFromText(transcript)" :disabled="!transcript.trim() || parsingNlp"
                            class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                            {{ parsingNlp ? '…' : 'Analyser' }}
                        </button>
                    </div>
                    <p v-if="!voiceSupported" class="mt-2 text-xs text-blue-700">
                        Saisie vocale non supportée (Chrome / Edge requis). Vous pouvez taper la phrase ci-dessus.
                    </p>
                    <p v-if="voiceError" class="mt-2 text-xs text-red-600">{{ voiceError }}</p>
                    <p v-if="nlpHint" class="mt-2 text-xs text-blue-800">{{ nlpHint }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
                    <!-- Type -->
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="form.type = 'expense'"
                            :class="['rounded-md py-2 text-sm font-medium border',
                                form.type === 'expense' ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-700 border-gray-200']">
                            Dépense
                        </button>
                        <button type="button" @click="form.type = 'income'"
                            :class="['rounded-md py-2 text-sm font-medium border',
                                form.type === 'income' ? 'bg-green-600 text-white border-green-600' : 'bg-white text-gray-700 border-gray-200']">
                            Revenu
                        </button>
                    </div>

                    <!-- Montant -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Montant (FCFA)</label>
                        <input v-model="form.amount" type="number" step="1" min="0" required
                            class="w-full rounded-md border-gray-300 text-lg" placeholder="0" />
                        <p v-if="form.errors.amount" class="text-xs text-red-600 mt-1">{{ form.errors.amount }}</p>
                    </div>

                    <!-- Catégorie -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                        <select v-model="form.category_id" class="w-full rounded-md border-gray-300">
                            <option :value="null">— Sans catégorie —</option>
                            <option v-for="c in filteredCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>

                    <!-- Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date & heure</label>
                        <input v-model="form.occurred_at" type="datetime-local" required
                            class="w-full rounded-md border-gray-300" />
                        <p v-if="form.errors.occurred_at" class="text-xs text-red-600 mt-1">{{ form.errors.occurred_at }}</p>
                    </div>

                    <!-- Mode de paiement -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mode de paiement</label>
                        <select v-model="form.payment_method" class="w-full rounded-md border-gray-300">
                            <option v-for="p in PAYMENT_METHODS" :key="p.value" :value="p.value">{{ p.label }}</option>
                        </select>
                    </div>

                    <!-- Bénéficiaire -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bénéficiaire / source</label>
                        <input v-model="form.beneficiary" type="text" maxlength="255"
                            class="w-full rounded-md border-gray-300" placeholder="Ex : maquis, maman, taxi…" />
                    </div>

                    <!-- Note -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                        <textarea v-model="form.note" rows="2" maxlength="2000"
                            class="w-full rounded-md border-gray-300"></textarea>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <Link :href="route('transactions.index')" class="text-sm text-gray-600 hover:underline">← Annuler</Link>
                        <button type="submit" :disabled="form.processing"
                            class="rounded-md bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                            {{ isEdit ? 'Mettre à jour' : 'Enregistrer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
