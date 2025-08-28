<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

class MovimentacaoPerdaSeed extends AbstractSeed
{
    public function run(): void
    {
        $movimentacoes = [];
        for ($i = 701; $i <= 1001; $i++) {
            $movimentacoes[] = [
                'id' => $i,
                'data' => date('Y-m-d', strtotime("-{$i} days")),
                'item_id' => rand(1, 210), 
                'quantidade' => rand(1, 500), 
                'observacao' => 'Observação opcional sobre a movimentação ' . $i,
                'tipo_movimentacao' => 'perda', 
                'lote_id'=> ($i - 700) % 300 + 1
            ];
        }

        
        $movimentacaoTable = $this->table('movimentacao');
        $movimentacaoTable->insert($movimentacoes)->save();
    }
}
