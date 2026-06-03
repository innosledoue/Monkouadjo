<?php

namespace App\Services\Ai;

use Carbon\Carbon;

/**
 * Parser regex pur pour SMS Mobile Money ivoiriens (OM / Wave / MoMo).
 * Aucune dépendance externe — utilisable offline.
 */
class IvorianSmsParser
{
    /**
     * @return array{
     *   provider: string,             // om|wave|momo|unknown
     *   type: string,                 // expense|income
     *   amount: float|null,
     *   fees: float|null,
     *   balance: float|null,
     *   beneficiary: string|null,
     *   transaction_id: string|null,
     *   occurred_at: string|null,     // ISO 8601
     *   payment_method: string,
     *   note: string,
     *   raw: string,
     *   confidence: float,
     * }
     */
    public function parse(string $sms): array
    {
        $sms = trim($sms);
        $lower = mb_strtolower($sms, 'UTF-8');

        $provider = $this->detectProvider($lower);
        $type = $this->detectType($lower);
        $amount = $this->extractAmount($sms);
        $fees = $this->extractFees($sms);
        $balance = $this->extractBalance($sms);
        $beneficiary = $this->extractBeneficiary($sms);
        $transactionId = $this->extractTransactionId($sms);
        $occurredAt = $this->extractDate($sms);

        $confidence = 0.0;
        if ($provider !== 'unknown') $confidence += 0.3;
        if ($amount !== null)        $confidence += 0.4;
        if ($transactionId)          $confidence += 0.15;
        if ($balance !== null)       $confidence += 0.15;

        return [
            'provider' => $provider,
            'type' => $type,
            'amount' => $amount,
            'fees' => $fees,
            'balance' => $balance,
            'beneficiary' => $beneficiary,
            'transaction_id' => $transactionId,
            'occurred_at' => $occurredAt,
            'payment_method' => $provider === 'unknown' ? 'other' : $provider,
            'note' => $this->buildNote($provider, $type, $beneficiary, $transactionId),
            'raw' => $sms,
            'confidence' => round(min(1.0, $confidence), 2),
        ];
    }

    private function detectProvider(string $lower): string
    {
        if (str_contains($lower, 'wave')) return 'wave';
        if (str_contains($lower, 'orange money') || preg_match('/\bom\b/u', $lower)) return 'om';
        if (str_contains($lower, 'mtn') && (str_contains($lower, 'momo') || str_contains($lower, 'mobile money'))) return 'momo';
        if (str_contains($lower, 'momo')) return 'momo';
        return 'unknown';
    }

    private function detectType(string $lower): string
    {
        $incomeMarkers = ['reçu', 'recu', 'reçoit', 'recoit', 'crédité', 'credite', 'vous avez reçu', 'vous a envoyé', 'a envoye'];
        foreach ($incomeMarkers as $m) {
            if (str_contains($lower, $m)) return 'income';
        }
        $expenseMarkers = ['envoyé', 'envoye', 'paiement', 'paye', 'retrait', 'achat', 'transfert', 'débité', 'debite'];
        foreach ($expenseMarkers as $m) {
            if (str_contains($lower, $m)) return 'expense';
        }
        return 'expense';
    }

    private function extractAmount(string $sms): ?float
    {
        // "Montant: 2 000 FCFA" / "2000 F" / "Vous avez envoyé 5 000 FCFA"
        if (preg_match('/(?:montant|envoy[ée]+|re[çc]u|paiement de|achat de|retrait de|transfert de)\D{0,15}([\d][\d ., ]{0,12}\d|\d)\s*(?:f|fcfa|cfa|xof|francs?)/iu', $sms, $m)) {
            return $this->cleanAmount($m[1]);
        }
        if (preg_match('/\b([\d][\d ., ]{2,12}\d|\d{3,8})\s*(?:f|fcfa|cfa|xof|francs?)\b/iu', $sms, $m)) {
            return $this->cleanAmount($m[1]);
        }
        return null;
    }

    private function extractFees(string $sms): ?float
    {
        if (preg_match('/(?:frais|commission)\D{0,10}([\d][\d ., ]{0,12}\d|\d)\s*(?:f|fcfa|cfa|xof)?/iu', $sms, $m)) {
            return $this->cleanAmount($m[1]);
        }
        return null;
    }

    private function extractBalance(string $sms): ?float
    {
        if (preg_match('/(?:nouveau\s+)?solde\D{0,10}([\d][\d ., ]{0,12}\d|\d)\s*(?:f|fcfa|cfa|xof)?/iu', $sms, $m)) {
            return $this->cleanAmount($m[1]);
        }
        return null;
    }

    private function extractBeneficiary(string $sms): ?string
    {
        // "à John DOE (+2250707...)" / "vers MARIE KONE" / "de KOUAME YAO"
        if (preg_match('/(?:à|a|vers|de|chez|pour)\s+([A-ZÀ-Ý][A-ZÀ-Ý\' -]{2,40}(?:\s+[A-ZÀ-Ý][A-ZÀ-Ý\' -]{2,40})?)/u', $sms, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private function extractTransactionId(string $sms): ?string
    {
        if (preg_match('/(?:trans(?:action)?|ref(?:erence)?|id|recu|reçu)[^A-Za-z0-9]{0,5}([A-Z0-9]{6,20})/iu', $sms, $m)) {
            return strtoupper($m[1]);
        }
        return null;
    }

    private function extractDate(string $sms): ?string
    {
        // "le 03/06/2026 à 11:08" / "03-06-2026 11:08"
        if (preg_match('/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})(?:\D{1,5}(\d{1,2})[:h](\d{2}))?/u', $sms, $m)) {
            try {
                $year = strlen($m[3]) === 2 ? '20' . $m[3] : $m[3];
                $time = isset($m[4]) ? sprintf('%02d:%02d:00', $m[4], $m[5]) : '00:00:00';
                return Carbon::createFromFormat('Y-m-d H:i:s', sprintf('%s-%02d-%02d %s', $year, $m[2], $m[1], $time))->toIso8601String();
            } catch (\Throwable) {
                return null;
            }
        }
        return null;
    }

    private function cleanAmount(string $raw): ?float
    {
        $clean = preg_replace('/[ .,]/', '', $raw);
        if ($clean === '' || !ctype_digit($clean)) return null;
        return (float) $clean;
    }

    private function buildNote(string $provider, string $type, ?string $beneficiary, ?string $txId): string
    {
        $parts = [strtoupper($provider) . ' — ' . ($type === 'income' ? 'reçu' : 'envoyé')];
        if ($beneficiary) $parts[] = $beneficiary;
        if ($txId) $parts[] = 'ref ' . $txId;
        return implode(' · ', $parts);
    }
}
