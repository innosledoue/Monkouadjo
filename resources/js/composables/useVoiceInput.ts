import { ref, onUnmounted } from 'vue';

/**
 * Composable Web Speech API (Chrome / Edge — fr-FR).
 * Pas de dépendance externe, fonctionne offline pour la transcription.
 * Fallback : si non supporté, isSupported = false → afficher message.
 */
export function useVoiceInput(lang = 'fr-FR') {
    const transcript = ref('');
    const listening = ref(false);
    const error = ref<string | null>(null);

    const SpeechRecognition: any = (window as any).SpeechRecognition || (window as any).webkitSpeechRecognition;
    const isSupported = !!SpeechRecognition;

    let recognition: any = null;

    function start() {
        if (!isSupported) {
            error.value = 'La saisie vocale n\'est pas supportée par ce navigateur. Utilisez Chrome ou Edge.';
            return;
        }
        error.value = null;
        transcript.value = '';
        recognition = new SpeechRecognition();
        recognition.lang = lang;
        recognition.interimResults = true;
        recognition.continuous = false;

        recognition.onresult = (event: any) => {
            let text = '';
            for (let i = 0; i < event.results.length; i++) {
                text += event.results[i][0].transcript;
            }
            transcript.value = text;
        };
        recognition.onerror = (e: any) => {
            error.value = e.error || 'Erreur de reconnaissance vocale.';
            listening.value = false;
        };
        recognition.onend = () => {
            listening.value = false;
        };

        recognition.start();
        listening.value = true;
    }

    function stop() {
        if (recognition) {
            try { recognition.stop(); } catch {}
        }
        listening.value = false;
    }

    onUnmounted(() => stop());

    return { transcript, listening, error, isSupported, start, stop };
}
