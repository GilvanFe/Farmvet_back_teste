<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

class MovimentacaoSeed extends AbstractSeed
{
    public function run(): void
    {
        $setores = ['GA', 'CPA', 'CCPA', 'PIS', 'API', 'AP', 'BM', 'BAC', 'PAR', 'VIR', 'MA', 'NAN', 'NAP', 'PP', 'PC', 'RA', 'RAS', 'SF', 'CAV', 'QUA', 'TCV', 'IH', 'NEC', 'UTI', 'MULTR', 'MULTA', 'FE', 'CCC', 'EME', 'GO', 'RX', 'CXGA', 'HUMAP', 'UFMS', 'SESAU', 'BCL', 'HRMS', 'FAMEZ', 'ZOO', 'BASE', 'ANVET'];

        $movimentacoes = [];
        for ($i = 1; $i <= 270; $i++) {
            $movimentacoes[] = [
                'id' => $i,
                'data' => date('Y-m-d', strtotime("-{$i} days")),
                'setor_id' => $setores[array_rand($setores)],
                'observacao' => 'Observação opcional sobre a movimentação ' . $i,
                'pessoa_requerente' => 'Nome do Requerente ' . $i,
                'requerimento' => 'Requerimento XYZ ' . $i,
                'tipo_movimentacao' => 'saida',
                'nome_animal' => 'Nome do Animal ' . $i,
                'ficha_clinica' => 'Ficha Clínica XYZ ' . $i,
            ];
        }



        $movimentacaoTable = $this->table('movimentacao');
        $movimentacaoTable->insert($movimentacoes)->save();
    }
}
