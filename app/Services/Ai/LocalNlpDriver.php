<?php

namespace App\Services\Ai;

use App\Models\Category;
use App\Services\Ai\Contracts\NlpDriver;
use Illuminate\Support\Str;

class LocalNlpDriver implements NlpDriver
{
    /**
     * Dictionnaire mots-clés → nom de catégorie (référence : seeder).
     * Inclut le français ivoirien vernaculaire.
     */
    private array $categoryKeywords = [
        'Alimentation'      => ['bouffer', 'bouffe', 'bouffé', 'manger', 'mangé', 'marché', 'marche', 'attieke', 'attiéké', 'riz', 'pain', 'cantine', 'repas', 'déjeuner', 'dejeuner', 'dîner', 'diner', 'petit-déj', 'snack'],
            'Maquis & Sorties'  => ['maquis', 'bar', 'bière', 'biere', 'sortie', 'restaurant', 'resto', 'pub', 'cabaret', 'boisson', 'sucrerie'],
        'Transport'         => ['taxi', 'gbaka', 'woro-woro', 'woro woro', 'bus', 'sotra', 'transport', 'essence', 'carburant', 'station', 'parking', 'autoroute', 'péage', 'peage', 'uber', 'yango'],
        'Téléphonie'        => ['crédit', 'credit', 'forfait', 'unités', 'unites', 'recharge', 'rechargement', 'internet', 'data', 'téléphone', 'telephone', 'mtn', 'orange', 'moov', 'pass'],
        'Loyer'             => ['loyer', 'bail', 'caution', 'logement'],
        'CIE (Électricité)' => ['cie', 'electricite', 'électricité', 'courant', 'facture electricite', 'facture cie'],
        'SODECI (Eau)'      => ['sodeci', 'eau', 'facture eau'],
        'Santé'             => ['pharmacie', 'medicament', 'médicament', 'docteur', 'médecin', 'medecin', 'hopital', 'hôpital', 'clinique', 'santé', 'sante', 'consultation', 'ordonnance'],
        'Éducation'         => ['école', 'ecole', 'scolarité', 'scolarite', 'cours', 'fournitures', 'livre', 'inscription', 'université', 'universite', 'fac'],
        'Famille'           => ['maman', 'papa', 'frère', 'frere', 'sœur', 'soeur', 'enfant', 'famille', 'parents', 'tonton', 'tata'],
        'Tontine'           => ['tontine', 'cotisation tontine'],
        'Habillement'       => ['habit', 'vêtement', 'vetement', 'chaussure', 'pagne', 'tissu', 'couture', 'tailleur'],
        'Loisirs'           => ['cinéma', 'cinema', 'jeu', 'jeux', 'loisir', 'sport', 'salle', 'concert'],
        'Frais bancaires'   => ['frais bancaire', 'commission', 'agios', 'frais retrait'],
        'Salaire'           => ['salaire', 'paie', 'paye', 'paiement salaire'],
        'Commerce'          => ['vente', 'commerce', 'business', 'chiffre affaire', 'recette'],
    ];

    private array $paymentKeywords = [
        'wave' => ['wave'],
        'om'   => ['orange money', 'om ', 'om,', 'om.', 'flooz om'],
        'momo' => ['mtn momo', 'momo', 'mtn money', 'mtn mobile money'],
        'card' => ['carte', 'visa', 'mastercard'],
        'bank' => ['virement', 'banque', 'chèque', 'cheque'],
    ];

    private array $incomeKeywords = [
        'reçu', 'recu', 'reçoit', 'recoit', 'encaissé', 'encaisse',
        'salaire', 'paie reçue', 'tontine perçue', 'tontine percue',
        'vente', 'recette', 'crédité', 'credite', 'crédite',
    ];

    public function extractEntities(string $text, ?int $userId = null): array
    {
        $original = $text;
        $lower = mb_strtolower($text, 'UTF-8');
        $normalized = $this->stripAccents($lower);

        $amount = $this->extractAmount($normalized);
        $type = $this->detectType($normalized);
        $paymentMethod = $this->detectPayment($normalized);
        [$categoryName, $catConfidence] = $this->detectCategory($normalized);
        $beneficiary = $this->detectBeneficiary($lower);

        $categoryId = null;
        if ($categoryName) {
            $category = Category::query()
                ->where('name', $categoryName)
                ->forUser($userId)
                ->first();
            $categoryId = $category?->id;
        }

        $confidence = 0.0;
        $confidence += $amount !== null ? 0.5 : 0.0;
        $confidence += $catConfidence * 0.3;
        $confidence += $paymentMethod !== 'cash' ? 0.1 : 0.0;
        $confidence = min(1.0, $confidence);

        return [
            'amount' => $amount,
            'type' => $type,
            'category_id' => $categoryId,
            'category_name' => $categoryName,
            'payment_method' => $paymentMethod,
            'beneficiary' => $beneficiary,
            'note' => Str::limit(trim($original), 200, ''),
            'confidence' => round($confidence, 2),
            'driver' => 'local',
        ];
    }

    private function extractAmount(string $text): ?float
    {
        // Patterns d'unités courantes en FCFA : "2000", "2 000", "2.000", "2k", "5 mille", "10 milles"
        // 1. "k" suffix : "2k", "10k"
        if (preg_match('/\b(\d+)\s*k\b/u', $text, $m)) {
            return (float) $m[1] * 1000;
        }
        // 2. "X mille" / "X milles" / "X 000"
        if (preg_match('/\b(\d+)\s*(?:mille|milles)\b/u', $text, $m)) {
            return (float) $m[1] * 1000;
        }
        // 3. Nombre simple avec séparateurs : "2 000", "2.000", "2,000", "10000"
        if (preg_match('/\b(\d{1,3}(?:[ .,]\d{3})+|\d{3,7})\b/u', $text, $m)) {
            $clean = preg_replace('/[ .,]/', '', $m[1]);
            return (float) $clean;
        }
        // 4. Nombre court : "500 francs", "300 fcfa"
        if (preg_match('/\b(\d{2,4})\s*(?:fcfa|francs?|f\b)/u', $text, $m)) {
            return (float) $m[1];
        }
        // 5. Tout nombre isolé >=100
        if (preg_match('/\b(\d{3,7})\b/u', $text, $m)) {
            return (float) $m[1];
        }
        return null;
    }

    private function detectType(string $text): string
    {
        foreach ($this->incomeKeywords as $kw) {
            if (str_contains($text, $this->stripAccents($kw))) {
                return 'income';
            }
        }
        return 'expense';
    }

    private function detectPayment(string $text): string
    {
        foreach ($this->paymentKeywords as $method => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($text, $kw)) {
                    return $method;
                }
            }
        }
        return 'cash';
    }

    /**
     * @return array{0: string|null, 1: float}
     */
    private function detectCategory(string $text): array
    {
        $bestName = null;
        $bestScore = 0;
        foreach ($this->categoryKeywords as $catName => $keywords) {
            foreach ($keywords as $kw) {
                $kwNorm = $this->stripAccents($kw);
                if (str_contains($text, $kwNorm)) {
                    $score = strlen($kwNorm);
                    if ($score > $bestScore) {
                        $bestScore = $score;
                        $bestName = $catName;
                    }
                }
            }
        }
        return [$bestName, $bestName ? min(1.0, $bestScore / 10) : 0.0];
    }

    private function detectBeneficiary(string $lowerText): ?string
    {
        // "envoyé à <name>", "envoyé pour <name>", "à <name>"
        if (preg_match('/(?:envoy[ée]+|donn[ée]+|pay[ée]+)\s+(?:à|a|au|pour|chez)\s+([a-zàâäéèêëîïôöùûüç\' -]{2,30})/u', $lowerText, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/\b(?:à|au|pour|chez)\s+(maman|papa|tonton|tata|patron|djo|frangin|sœur|soeur|frère|frere|copain|copine|ami|amie)\b/u', $lowerText, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private function stripAccents(string $s): string
    {
        $from = ['à','â','ä','á','ã','å','é','è','ê','ë','î','ï','í','ô','ö','ó','õ','ù','û','ü','ú','ç','ñ'];
        $to   = ['a','a','a','a','a','a','e','e','e','e','i','i','i','o','o','o','o','u','u','u','u','c','n'];
        return str_replace($from, $to, $s);
    }
}
