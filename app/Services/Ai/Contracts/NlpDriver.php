<?php

namespace App\Services\Ai\Contracts;

interface NlpDriver
{
    /**
     * Extrait les entités financières d'un texte libre (vocal ou tapé).
     *
     * @return array{
     *   amount: float|null,
     *   type: string,            // 'expense'|'income'
     *   category_id: int|null,
     *   payment_method: string,  // cash|om|momo|wave|...
     *   beneficiary: string|null,
     *   note: string|null,
     *   confidence: float,       // 0..1
     *   driver: string,
     * }
     */
    public function extractEntities(string $text, ?int $userId = null): array;
}
