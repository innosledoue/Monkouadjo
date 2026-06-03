<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
}>();
</script>

<template>
    <Head title="MonKouadjo — Maîtrisez vos finances" />

    <div class="min-h-screen bg-white font-sans antialiased overflow-x-hidden">

        <!-- Formes décoratives arrière-plan -->
        <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-[600px] h-[600px] rounded-full bg-blue-50 opacity-60"></div>
            <div class="absolute top-1/2 -left-60 w-[500px] h-[500px] rounded-full bg-blue-50 opacity-40"></div>
            <div class="absolute -bottom-40 right-1/3 w-[400px] h-[400px] rounded-full bg-blue-100 opacity-30"></div>
        </div>

        <!-- NAVBAR -->
        <nav class="relative z-10 flex items-center justify-between px-6 py-5 max-w-7xl mx-auto">
            <!-- Logo -->
            <div class="flex items-center gap-3">
                <img src="/images/logo_monkouadjo.png" alt="MonKouadjo" class="h-12 w-auto" />
                <span class="text-xl font-extrabold text-blue-900 tracking-tight">MonKouadjo</span>
            </div>

            <!-- Nav links -->
            <div class="flex items-center gap-3">
                <template v-if="canLogin">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-lg bg-blue-900 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-800 transition-colors"
                    >
                        Tableau de bord
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="rounded-lg px-5 py-2 text-sm font-semibold text-blue-900 border border-blue-200 hover:bg-blue-50 transition-colors"
                        >
                            Connexion
                        </Link>
                        <Link
                            :href="route('register')"
                            class="rounded-lg bg-blue-900 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-800 transition-colors shadow-sm"
                        >
                            S'inscrire
                        </Link>
                    </template>
                </template>
            </div>
        </nav>

        <!-- HERO SECTION -->
        <section class="relative z-10 max-w-7xl mx-auto px-6 pt-16 pb-24 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            <!-- Texte gauche -->
            <div class="space-y-7">
                <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 border border-blue-200 px-4 py-1.5 text-sm font-medium text-blue-700">
                    <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                    Gestion financière pour le quotidien ivoirien
                </div>

                <h1 class="text-4xl sm:text-5xl font-extrabold text-blue-900 leading-tight">
                    Maîtrisez vos finances avec
                    <span class="text-blue-600">MonKouadjo</span>
                </h1>

                <p class="text-lg text-slate-600 leading-relaxed max-w-lg">
                    Gérez votre budget simplement et atteignez vos objectifs financiers.
                    Suivez vos dépenses, importez vos SMS Mobile Money et gardez le contrôle.
                </p>

                <div class="flex flex-wrap gap-4">
                    <Link
                        :href="route('register')"
                        class="rounded-xl bg-blue-900 px-8 py-3.5 text-base font-bold text-white hover:bg-blue-800 transition-all shadow-lg shadow-blue-900/20 hover:shadow-blue-900/30 hover:-translate-y-0.5 transform"
                    >
                        Essayer gratuitement
                    </Link>
                    <Link
                        :href="route('login')"
                        class="rounded-xl border-2 border-blue-200 px-8 py-3.5 text-base font-semibold text-blue-900 hover:bg-blue-50 transition-colors"
                    >
                        Se connecter
                    </Link>
                </div>

                <!-- Stats rapides -->
                <div class="flex flex-wrap gap-8 pt-4 border-t border-gray-100">
                    <div>
                        <div class="text-2xl font-bold text-blue-900">OM · Wave · MoMo</div>
                        <div class="text-sm text-slate-500 mt-0.5">Import SMS automatique</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-blue-900">100% gratuit</div>
                        <div class="text-sm text-slate-500 mt-0.5">Sans abonnement</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-blue-900">Offline</div>
                        <div class="text-sm text-slate-500 mt-0.5">Fonctionne sans réseau</div>
                    </div>
                </div>
            </div>

            <!-- Mockup droite -->
            <div class="relative flex justify-center lg:justify-end">
                <!-- Carte principale (téléphone) -->
                <div class="relative">
                    <!-- Téléphone -->
                    <div class="w-64 bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
                        <!-- Barre de statut mockup -->
                        <div class="bg-blue-900 px-4 py-3 flex items-center justify-between">
                            <span class="text-white text-xs font-semibold">MonKouadjo</span>
                            <div class="flex gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-300"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-300"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-300"></span>
                            </div>
                        </div>
                        <div class="p-4 bg-blue-900 pb-5">
                            <div class="text-blue-200 text-xs mb-1">Solde du mois</div>
                            <div class="text-white text-2xl font-bold">245 800 FCFA</div>
                            <div class="flex gap-3 mt-3">
                                <div class="flex-1 bg-blue-800 rounded-lg p-2">
                                    <div class="text-blue-300 text-xs">Revenus</div>
                                    <div class="text-green-400 text-sm font-bold">+380k</div>
                                </div>
                                <div class="flex-1 bg-blue-800 rounded-lg p-2">
                                    <div class="text-blue-300 text-xs">Dépenses</div>
                                    <div class="text-red-400 text-sm font-bold">-134k</div>
                                </div>
                            </div>
                        </div>
                        <!-- Graphique barres mockup -->
                        <div class="px-4 py-4 bg-white">
                            <div class="text-xs font-semibold text-gray-600 mb-3">Évolution 6 mois</div>
                            <div class="flex items-end gap-1.5 h-16">
                                <div class="flex-1 flex flex-col items-center gap-0.5">
                                    <div class="w-full bg-blue-200 rounded-t" style="height: 60%"></div>
                                    <div class="text-gray-400" style="font-size:8px">Jan</div>
                                </div>
                                <div class="flex-1 flex flex-col items-center gap-0.5">
                                    <div class="w-full bg-blue-400 rounded-t" style="height: 75%"></div>
                                    <div class="text-gray-400" style="font-size:8px">Fév</div>
                                </div>
                                <div class="flex-1 flex flex-col items-center gap-0.5">
                                    <div class="w-full bg-blue-300 rounded-t" style="height: 50%"></div>
                                    <div class="text-gray-400" style="font-size:8px">Mar</div>
                                </div>
                                <div class="flex-1 flex flex-col items-center gap-0.5">
                                    <div class="w-full bg-blue-600 rounded-t" style="height: 85%"></div>
                                    <div class="text-gray-400" style="font-size:8px">Avr</div>
                                </div>
                                <div class="flex-1 flex flex-col items-center gap-0.5">
                                    <div class="w-full bg-blue-400 rounded-t" style="height: 65%"></div>
                                    <div class="text-gray-400" style="font-size:8px">Mai</div>
                                </div>
                                <div class="flex-1 flex flex-col items-center gap-0.5">
                                    <div class="w-full bg-blue-900 rounded-t" style="height: 100%"></div>
                                    <div class="text-gray-400" style="font-size:8px">Juin</div>
                                </div>
                            </div>
                        </div>
                        <!-- Transactions mockup -->
                        <div class="px-4 pb-4 space-y-2">
                            <div class="text-xs font-semibold text-gray-600 mb-2">Récent</div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center">
                                        <span style="font-size:10px">🛒</span>
                                    </div>
                                    <span class="text-xs text-gray-700">Marché</span>
                                </div>
                                <span class="text-xs font-semibold text-red-500">-5 000</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center">
                                        <span style="font-size:10px">💰</span>
                                    </div>
                                    <span class="text-xs text-gray-700">Salaire</span>
                                </div>
                                <span class="text-xs font-semibold text-green-500">+380 000</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span style="font-size:10px">🚕</span>
                                    </div>
                                    <span class="text-xs text-gray-700">Taxi</span>
                                </div>
                                <span class="text-xs font-semibold text-red-500">-1 500</span>
                            </div>
                        </div>
                    </div>

                    <!-- Badge flottant -->
                    <div class="absolute -top-4 -right-4 bg-white rounded-xl shadow-lg border border-gray-100 px-3 py-2 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-base">✅</div>
                        <div>
                            <div class="text-xs font-bold text-gray-800">Sync réussie</div>
                            <div class="text-gray-400" style="font-size:10px">3 transactions</div>
                        </div>
                    </div>

                    <!-- Badge Wave -->
                    <div class="absolute -bottom-4 -left-6 bg-white rounded-xl shadow-lg border border-gray-100 px-3 py-2 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-base">📱</div>
                        <div>
                            <div class="text-xs font-bold text-gray-800">SMS Wave importé</div>
                            <div class="text-gray-400" style="font-size:10px">Auto-catégorisé</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FEATURE CARDS -->
        <section class="relative z-10 bg-blue-900 py-20">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-white">Tout ce dont vous avez besoin</h2>
                    <p class="mt-3 text-blue-200 max-w-xl mx-auto">
                        Conçu pour la réalité financière ivoirienne — Mobile Money, tontines, dettes.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <!-- Carte 1 -->
                    <div class="bg-white/10 backdrop-blur rounded-2xl p-6 border border-white/10 hover:bg-white/15 transition-colors">
                        <div class="w-12 h-12 rounded-xl bg-blue-600 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Suivi automatisé</h3>
                        <p class="text-blue-200 text-sm leading-relaxed">
                            Importez vos SMS Orange Money, Wave et MTN MoMo en un clic. Vos dépenses sont enregistrées automatiquement.
                        </p>
                    </div>

                    <!-- Carte 2 -->
                    <div class="bg-white/10 backdrop-blur rounded-2xl p-6 border border-white/10 hover:bg-white/15 transition-colors">
                        <div class="w-12 h-12 rounded-xl bg-blue-600 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Catégorisation intelligente</h3>
                        <p class="text-blue-200 text-sm leading-relaxed">
                            L'IA locale reconnaît automatiquement vos types de dépenses : maquis, transport, famille, télécoms…
                        </p>
                    </div>

                    <!-- Carte 3 -->
                    <div class="bg-white/10 backdrop-blur rounded-2xl p-6 border border-white/10 hover:bg-white/15 transition-colors">
                        <div class="w-12 h-12 rounded-xl bg-blue-600 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Objectifs d'épargne</h3>
                        <p class="text-blue-200 text-sm leading-relaxed">
                            Définissez des enveloppes budgétaires, suivez vos tontines et gérez vos dettes pour atteindre vos objectifs.
                        </p>
                    </div>

                </div>
            </div>
        </section>

        <!-- SECTION BONUS : autres fonctionnalités -->
        <section class="relative z-10 py-20 max-w-7xl mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-blue-900">Une app complète pour votre vie financière</h2>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="rounded-2xl bg-blue-50 p-5 text-center hover:bg-blue-100 transition-colors">
                    <div class="text-3xl mb-2">📊</div>
                    <div class="text-sm font-semibold text-blue-900">Rapports & Stats</div>
                </div>
                <div class="rounded-2xl bg-blue-50 p-5 text-center hover:bg-blue-100 transition-colors">
                    <div class="text-3xl mb-2">🤝</div>
                    <div class="text-sm font-semibold text-blue-900">Tontines</div>
                </div>
                <div class="rounded-2xl bg-blue-50 p-5 text-center hover:bg-blue-100 transition-colors">
                    <div class="text-3xl mb-2">💳</div>
                    <div class="text-sm font-semibold text-blue-900">Dettes & Prêts</div>
                </div>
                <div class="rounded-2xl bg-blue-50 p-5 text-center hover:bg-blue-100 transition-colors">
                    <div class="text-3xl mb-2">📶</div>
                    <div class="text-sm font-semibold text-blue-900">Mode hors-ligne</div>
                </div>
            </div>
        </section>

        <!-- CTA FINAL -->
        <section class="relative z-10 py-20 px-6">
            <div class="max-w-2xl mx-auto text-center bg-blue-900 rounded-3xl py-14 px-8 shadow-2xl shadow-blue-900/20">
                <h2 class="text-3xl font-extrabold text-white mb-4">
                    Prêt à prendre le contrôle de votre argent ?
                </h2>
                <p class="text-blue-200 mb-8">
                    Créez votre compte gratuitement et commencez à suivre vos finances en quelques minutes.
                </p>
                <Link
                    :href="route('register')"
                    class="inline-block rounded-xl bg-white px-10 py-4 text-base font-bold text-blue-900 hover:bg-blue-50 transition-colors shadow-lg"
                >
                    Essayer gratuitement →
                </Link>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="relative z-10 border-t border-gray-100 py-8 px-6 text-center">
            <div class="flex items-center justify-center mb-3">
                <img src="/images/logo_monkouadjo.png" alt="MonKouadjo" class="h-8 w-auto" />
            </div>
            <p class="text-sm text-gray-400">
                © {{ new Date().getFullYear() }} MonKouadjo — Gestion financière personnelle pour la Côte d'Ivoire
            </p>
        </footer>

    </div>
</template>
