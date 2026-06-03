import { onMounted, onUnmounted, ref } from 'vue';
import { pendingCount, flushQueue } from '@/offline';

export function useOfflineStatus() {
    const isOnline  = ref(navigator.onLine);
    const pending   = ref(0);
    const syncing   = ref(false);
    const lastSync  = ref<string | null>(null);

    async function refreshPending() {
        pending.value = await pendingCount();
    }

    async function syncNow() {
        if (!isOnline.value || syncing.value) return;
        syncing.value = true;
        const csrf = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
        const { sent } = await flushQueue(csrf);
        if (sent > 0) {
            lastSync.value = new Date().toLocaleTimeString('fr-FR');
            window.location.reload();
        }
        await refreshPending();
        syncing.value = false;
    }

    function onOnline()  { isOnline.value = true;  syncNow(); }
    function onOffline() { isOnline.value = false; }

    onMounted(async () => {
        await refreshPending();
        window.addEventListener('online',  onOnline);
        window.addEventListener('offline', onOffline);
    });

    onUnmounted(() => {
        window.removeEventListener('online',  onOnline);
        window.removeEventListener('offline', onOffline);
    });

    return { isOnline, pending, syncing, lastSync, syncNow, refreshPending };
}
