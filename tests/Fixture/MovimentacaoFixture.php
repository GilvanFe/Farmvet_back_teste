<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class MovimentacaoFixture extends TestFixture
{
    public string $table = 'movimentacao';

    public function init(): void
    {
        $this->records = [
            [
                'data' => '2025-05-01',
                'observacao' => 'Entrada lote 1',
                'pessoa_requerente' => 'João da Silva',
                'requerimento' => 'REQ001',
                'tipo_movimentacao' => 'entrada',
                'subtipo_movimentacao' => 'compra',
                'fornecedor_id' => 1,
                'log_lotes_movimentacao' => 'Entrada lote 1',
                'via_compra' => 'Licitação',
                'documento_origem' => 'NF001',
            ],
            [
                'data' => '2025-05-02',
                'observacao' => 'Entrada lote 2',
                'pessoa_requerente' => 'Maria Oliveira',
                'requerimento' => 'REQ002',
                'tipo_movimentacao' => 'entrada',
                'subtipo_movimentacao' => 'compra',
                'fornecedor_id' => 1,
                'log_lotes_movimentacao' => 'Entrada lote 2',
                'via_compra' => 'Licitação',
                'documento_origem' => 'NF002',
            ],
            [
                'data' => '2025-05-03',
                'setor_id' => '002',
                'observacao' => 'Saída do lote 2',
                'pessoa_requerente' => 'Carlos Souza',
                'requerimento' => 'REQ003',
                'tipo_movimentacao' => 'saida',
                'subtipo_movimentacao' => 'perda',
                'fornecedor_id' => null,
                'log_lotes_movimentacao' => 'Saída lote 2',
                'via_compra' => null,
                'documento_origem' => null,
            ],
            [
                'data' => '2025-05-03',
                'observacao' => 'Entrada lote 3',
                'pessoa_requerente' => 'Luciana Mendes',
                'requerimento' => 'REQ004',
                'tipo_movimentacao' => 'saida',
                'subtipo_movimentacao' => 'vencimento',
                'fornecedor_id' => 1,
                'log_lotes_movimentacao' => 'Entrada lote 3',
                'via_compra' => 'Licitação',
                'documento_origem' => 'NF003',
            ]
        ];


        //Teste de exaustão para listagem
        for ($i = 0; $i < 120; $i++) {
            $isEntrada = ($i % 2);
            $currentDate = date('Y-m-d', strtotime("-$i days"));

            $this->records[] = [
                'data' => $currentDate,
                'setor_id' => $isEntrada ? null : '002',
                'observacao' => 'Observação ' . ($i + 1),
                'pessoa_requerente' => 'Requerente ' . ($i + 1),
                'requerimento' => 'REQ' . ($i + 1000),
                'tipo_movimentacao' => $isEntrada ? 'entrada' : 'saida',
                'subtipo_movimentacao' => $isEntrada ? 'compra' : 'consumo famez',
                'fornecedor_id' => $isEntrada ? 1 : null,
                'log_lotes_movimentacao' => ($isEntrada ? 'Entrada' : 'Saída') . ' lote ' . ($i + 1),
                'via_compra' => $isEntrada ? 'VC' . ($i +1) : null,
                'documento_origem' => $isEntrada ? 'DO' . ($i + 1) : null,
                'created' => $currentDate . ' 10:00:00',
                'modified' => $currentDate . ' 10:00:00',
                'nome_animal' => $isEntrada ? null : 'Animal ' . ($i + 1),
                'ficha_clinica' => $isEntrada ? null : 'FICHA ' . ($i + 1)
            ];
        }

        parent::init();
    }
}
