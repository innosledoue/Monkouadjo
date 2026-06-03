<?php

namespace Tests\Unit;

use App\Services\Ai\IvorianSmsParser;
use PHPUnit\Framework\TestCase;

class IvorianSmsParserTest extends TestCase
{
    private IvorianSmsParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new IvorianSmsParser();
    }

    public function test_parses_wave_received_sms(): void
    {
        $sms = "Vous avez reçu 25 000 FCFA de KOUAME YAO. Frais: 0 F. Nouveau solde: 47 320 FCFA. Trans: WV2026060311080012. le 03/06/2026 à 11:08 - Wave";
        $r = $this->parser->parse($sms);

        $this->assertSame('wave', $r['provider']);
        $this->assertSame('income', $r['type']);
        $this->assertSame(25000.0, $r['amount']);
        $this->assertSame(0.0, $r['fees']);
        $this->assertSame(47320.0, $r['balance']);
        $this->assertSame('WV2026060311080012', $r['transaction_id']);
        $this->assertSame('wave', $r['payment_method']);
    }

    public function test_parses_orange_money_payment_sms(): void
    {
        $sms = "Orange Money: Paiement de 5 500 FCFA à MAQUIS LE BARON effectué. Frais: 50 F. Solde: 12 800 FCFA. Ref: OM9981234567. 03/06/2026 12:30";
        $r = $this->parser->parse($sms);

        $this->assertSame('om', $r['provider']);
        $this->assertSame('expense', $r['type']);
        $this->assertSame(5500.0, $r['amount']);
        $this->assertSame(50.0, $r['fees']);
        $this->assertSame(12800.0, $r['balance']);
        $this->assertSame('OM9981234567', $r['transaction_id']);
    }

    public function test_parses_momo_transfer_sms(): void
    {
        $sms = "MTN MoMo: Transfert de 10 000 FCFA envoyé à MARIE BAMBA (+22507070707). Frais 100 F. Solde 35 200 FCFA. Trans MM77881234. 03/06/2026 13:45";
        $r = $this->parser->parse($sms);

        $this->assertSame('momo', $r['provider']);
        $this->assertSame('expense', $r['type']);
        $this->assertSame(10000.0, $r['amount']);
        $this->assertSame(100.0, $r['fees']);
        $this->assertSame(35200.0, $r['balance']);
    }

    public function test_unknown_provider_returns_other(): void
    {
        $sms = "Bonjour, votre commande de 1500 F a été expédiée.";
        $r = $this->parser->parse($sms);
        $this->assertSame('unknown', $r['provider']);
        $this->assertSame('other', $r['payment_method']);
    }

    public function test_confidence_score_reflects_extraction_quality(): void
    {
        $rich = $this->parser->parse("Wave: Paiement de 5 000 FCFA. Solde: 10 000 F. Trans: WV12345678. 03/06/2026 12:00");
        $poor = $this->parser->parse("Coucou ça va ?");
        $this->assertGreaterThan($poor['confidence'], $rich['confidence']);
    }
}
