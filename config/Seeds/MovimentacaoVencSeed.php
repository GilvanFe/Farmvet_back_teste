<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

class MovimentacaoVencSeed extends AbstractSeed
{
    public function run(): void
    {
        $movimentacoes = [];
        for ($i = 280; $i <= 700; $i++) {
            $movimentacoes[] = [
                'id' => $i,
                'data' => date('Y-m-d', strtotime("-{$i} days")),
                'item_id' => 1, 
                'observacao' => 'Observação opcional sobre a movimentação ' . $i,
                'tipo_movimentacao' => 'vencimento', 
                'lote_id'=> ($i - 279) % 300 + 1,
            ];
        }

        
        $movimentacaoTable = $this->table('movimentacao');
        $movimentacaoTable->insert($movimentacoes)->save();
    }
}
