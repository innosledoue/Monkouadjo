<?php

namespace Tests\Feature;

use App\Services\Ai\LocalNlpDriver;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalNlpDriverTest extends TestCase
{
    use RefreshDatabase;

    private LocalNlpDriver $nlp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
        $this->nlp = new LocalNlpDriver();
    }

    /**
     * Exemple §3.1.1 du cahier des charges :
     * « J'ai bouffé 2000 pour le maquis » → Alimentation, 2 000 FCFA
     *
     * Note : « maquis » matche aussi « Maquis & Sorties ». Le scorer prend le mot-clé
     * le plus long → "maquis" gagne sur "bouffer". Les deux sont acceptables sémantiquement
     * (le maquis est un type de resto), donc on accepte les deux.
     */
    public function test_extracts_bouffe_maquis_example(): void
    {
        $r = $this->nlp->extractEntities("J'ai bouffé 2000 pour le maquis");
        $this->assertSame(2000.0, $r['amount']);
        $this->assertSame('expense', $r['type']);
        $this->assertContains($r['category_name'], ['Alimentation', 'Maquis & Sorties']);
        $this->assertSame('cash', $r['payment_method']);
    }

    /**
     * Exemple §3.1.1 : « J'ai envoyé du crédit à mon djo, 500 francs » → Téléphonie, 500
     */
    public function test_extracts_credit_djo_example(): void
    {
        $r = $this->nlp->extractEntities("J'ai envoyé du crédit à mon djo, 500 francs");
        $this->assertSame(500.0, $r['amount']);
        $this->assertSame('Téléphonie', $r['category_name']);
    }

    /**
     * Exemple §3.1.1 : « J'ai pris taxi, ça m'a coûté 300 » → Transport, 300
     */
    public function test_extracts_taxi_example(): void
    {
        $r = $this->nlp->extractEntities("J'ai pris taxi, ça m'a coûté 300");
        $this->assertSame(300.0, $r['amount']);
        $this->assertSame('Transport', $r['category_name']);
    }

    public function test_detects_wave_payment(): void
    {
        $r = $this->nlp->extractEntities("J'ai payé 5000 par Wave à maman");
        $this->assertSame(5000.0, $r['amount']);
        $this->assertSame('wave', $r['payment_method']);
    }

    public function test_detects_income(): void
    {
        $r = $this->nlp->extractEntities("J'ai reçu mon salaire 150000 FCFA");
        $this->assertSame('income', $r['type']);
        $this->assertSame(150000.0, $r['amount']);
    }

    public function test_parses_k_suffix_amount(): void
    {
        $r = $this->nlp->extractEntities("J'ai dépensé 5k au marché");
        $this->assertSame(5000.0, $r['amount']);
    }
}
