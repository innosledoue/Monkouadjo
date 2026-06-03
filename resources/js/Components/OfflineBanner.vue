<script setup lang="ts">
import { useOfflineStatus } from '@/composables/useOfflineStatus';

const { isOnline, pending, syncing, lastSync, syncNow } = useOfflineStatus();
</script>

<template>
    <!-- Bandeau hors-ligne -->
    <div v-if="!isOnline"
        class="flex items-center justify-between bg-orange-500 px-4 py-2 text-sm font-medium text-white">
        <span>
            Hors ligne
            <span v-if="pending > 0" class="ml-2 rounded-full bg-white px-2 py-0.5 text-xs font-bold text-orange-600">
                {{ pending }} en attente
            </span>
        </span>
        <span class="text-xs opacity-80">Les transactions seront envoyées au retour réseau.</span>
    </div>

    <!-- Bandeau de retour en ligne avec syncs en attente -->
    <div v-else-if="pending > 0"
        class="flex items-center justify-between bg-blue-600 px-4 py-2 text-sm font-medium text-white">
        <span>
            {{ pending }} transaction{{ pending > 1 ? 's' : '' }} en attente de synchronisation
        </span>
        <button @click="syncNow" :disabled="syncing"
            class="rounded-md bg-white px-3 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-50 disabled:opacity-60">
            {{ syncing ? 'Synchronisation…' : 'Synchroniser maintenant' }}
        </button>
    </div>

    <!-- Confirmation dernière sync -->
    <div v-else-if="lastSync"
        class="bg-green-600 px-4 py-1.5 text-center text-xs font-medium text-white">
        Synchronisé à {{ lastSync }}
    </div>
</template>
