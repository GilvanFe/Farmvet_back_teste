<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class LoteFixture extends TestFixture
{
    public string $table = 'lote';

    public function init(): void
    {
        $this->records = [
            [
                'data_vencimento' => '2025-12-31',
                'data_de_recebimento' => '2025-05-01',
                'quantidade' => 350,
                'valor_unitario' => 5.00,
                'valor_total' => 250.00,
                'item_id' => 1,
                'numero_lote' => 'L001',
                'is_ativo' => true
            ],
            [
                'data_vencimento' => '2025-12-31',
                'data_de_recebimento' => '2025-05-02',
                'quantidade' => -1,
                'valor_unitario' => 6.00,
                'valor_total' => 180.00,
                'item_id' => 1,
                'numero_lote' => 'L002',
                'is_ativo' => true
            ],
            [
                'data_vencimento' => '2025-06-30',
                'data_de_recebimento' => '2025-05-03',
                'quantidade' => 180,
                'valor_unitario' => 1.50,
                'valor_total' => 45.00,
                'item_id' => 2,
                'numero_lote' => 'M001',
                'is_ativo' => false
            ],
        ];
        for ($i = 0; $i < 120; $i++) {
            $this->records[] = [
                'data_vencimento' => '2025-06-30',
                'data_de_recebimento' => '2025-05-03',
                'quantidade' => 180,
                'valor_unitario' => 1.50,
                'valor_total' => 45.00,
                'item_id' => 2,
                'numero_lote' => 'TEX00' . $i,
                'is_ativo' => true
            ];
        };
        parent::init();
    }
}
