<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class LoteMovimentacaoFixture extends TestFixture
{
    public string $table = 'lote_movimentacao';

    public function init(): void
    {
        $this->records = [
            [
                'lote_id' => 100,
                'movimentacao_id' => 100,
                'quantidade' => 50, // entrada completa
                'created' => '2025-05-01 08:00:00',
                'modified' => '2025-05-01 08:30:00',
            ],
            [
                'lote_id' => 102,
                'movimentacao_id' => 102,
                'quantidade' => 50, // entrada
                'created' => '2025-05-02 09:00:00',
                'modified' => '2025-05-02 09:15:00',
            ],
            [
                'lote_id' => 102,
                'movimentacao_id' => 103,
                'quantidade' => 20, // saída
                'created' => '2025-05-03 09:30:00',
                'modified' => null,
            ],
            [
                'lote_id' => 103,
                'movimentacao_id' => 104,
                'quantidade' => 30, // entrada
                'created' => '2025-05-03 10:00:00',
                'modified' => null,
            ],
        ];
        for ($i = 0; $i < 120; $i++) {
            $isEntrada = ($i % 2);
            $movimentacaoId = $i + 5;
            $loteId = $isEntrada ? ($i + 5) : ($i % 3) + 1;

            $this->records[] = [
                'lote_id' => $i+1,
                'movimentacao_id' => $movimentacaoId,
                'quantidade' => $isEntrada ? (($i % 10) + 10000) : (($i % 5) + 1),
                'created' => date('Y-m-d H:i:s', strtotime("-$i days 10:00:00")),
                'modified' => $isEntrada ? date('Y-m-d H:i:s', strtotime("-$i days 10:30:00")) : null,
            ];
        }

        parent::init();
    }
}
