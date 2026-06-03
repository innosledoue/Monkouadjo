<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Connexion — MonKouadjo" />

    <div class="min-h-screen flex">

        <!-- Panneau gauche — Branding -->
        <div class="hidden lg:flex lg:w-1/2 bg-blue-900 flex-col justify-between p-12 relative overflow-hidden">
            <!-- Formes décoratives -->
            <div class="absolute -top-20 -right-20 w-72 h-72 rounded-full bg-blue-800 opacity-50"></div>
            <div class="absolute -bottom-20 -left-10 w-80 h-80 rounded-full bg-blue-800 opacity-40"></div>

            <!-- Logo + Nom -->
            <div class="relative z-10 flex items-center gap-3">
                <img src="/images/logo_monkouadjo.png" alt="MonKouadjo" class="h-12 w-auto" />
                <span class="text-2xl font-extrabold text-white tracking-tight">MonKouadjo</span>
            </div>

            <!-- Texte central -->
            <div class="relative z-10 space-y-6">
                <h1 class="text-4xl font-extrabold text-white leading-tight">
                    Maîtrisez vos<br/>finances au quotidien
                </h1>
                <p class="text-blue-200 text-lg leading-relaxed max-w-sm">
                    Suivez vos dépenses, gérez vos budgets et prenez de meilleures décisions financières.
                </p>

                <!-- Features -->
                <ul class="space-y-4 mt-6">
                    <li class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="text-blue-100 text-sm">Import SMS Orange Money, Wave & MoMo</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="text-blue-100 text-sm">Rapports et statistiques détaillés</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="text-blue-100 text-sm">Fonctionne hors-ligne, partout</span>
                    </li>
                </ul>
            </div>

            <p class="relative z-10 text-blue-400 text-xs">
                © {{ new Date().getFullYear() }} MonKouadjo — Côte d'Ivoire
            </p>
        </div>

        <!-- Panneau droit — Formulaire -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center px-6 py-12 sm:px-12 bg-white">
            <div class="max-w-md w-full mx-auto">

                <!-- Logo mobile -->
                <div class="flex items-center gap-3 mb-10 lg:hidden">
                    <img src="/images/logo_monkouadjo.png" alt="MonKouadjo" class="h-10 w-auto" />
                    <span class="text-xl font-extrabold text-blue-900">MonKouadjo</span>
                </div>

                <div class="mb-8">
                    <h2 class="text-3xl font-extrabold text-gray-900">Bon retour 👋</h2>
                    <p class="mt-2 text-gray-500 text-sm">Connectez-vous à votre compte pour continuer.</p>
                </div>

                <!-- Message status -->
                <div v-if="status" class="mb-6 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm font-medium text-green-700">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Adresse e-mail
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="vous@exemple.com"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all"
                        />
                        <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <!-- Mot de passe -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-sm font-semibold text-gray-700">
                                Mot de passe
                            </label>
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                            >
                                Mot de passe oublié ?
                            </Link>
                        </div>
                        <div class="relative">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 pr-12 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all"
                            />
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-600">{{ form.errors.password }}</p>
                    </div>

                    <!-- Se souvenir de moi -->
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        <span class="text-sm text-gray-600">Se souvenir de moi</span>
                    </label>

                    <!-- Bouton -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-xl bg-blue-900 px-6 py-3.5 text-sm font-bold text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-60 transition-all shadow-lg shadow-blue-900/20 hover:-translate-y-0.5 transform"
                    >
                        <span v-if="form.processing">Connexion en cours…</span>
                        <span v-else>Se connecter</span>
                    </button>
                </form>

                <!-- Lien inscription -->
                <p class="mt-8 text-center text-sm text-gray-500">
                    Pas encore de compte ?
                    <Link :href="route('register')" class="font-semibold text-blue-700 hover:text-blue-900">
                        Créer un compte
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>
