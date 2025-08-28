<?php

namespace App\Service;

use App\Model\Table\ItemTable;
use App\Model\Table\LotesMovimentacoesTable;
use App\Model\Table\LoteTable;
use App\Model\Table\MovimentacaoTable;
use App\Util\ExcelExporter;
use Cake\I18n\FrozenDate;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use DateMalformedStringException;
use DateTime;
use DateTimeImmutable;
use PhpOffice\PhpSpreadsheet\Exception;

class RelatorioService
{
    private LoteTable $loteTable;
    private ItemTable $itemTable;
    private MovimentacaoTable $movimentacaoTable;
    private LotesMovimentacoesTable $lotesMovimentacoesTable;

    public function __construct()
    {
        $this->loteTable = TableRegistry::getTableLocator()->get('Lote');
        $this->itemTable = TableRegistry::getTableLocator()->get('Item');
        $this->movimentacaoTable = TableRegistry::getTableLocator()->get('Movimentacao');
        $this->lotesMovimentacoesTable = TableRegistry::getTableLocator()->get('LotesMovimentacoes');
    }

    /**
     * Retorna a posição atual do estoque agrupado por item.
     * Apenas lotes ativos são considerados.
     *
     * @param String|null $tipo_item
     * @param string|null $unidade
     * @return array
     */
    public function getPosicaoEstoqueAtual(string $tipo_item = null, string $unidade = null): array
    {
        $loteQuery = $this->loteTable->find()
            ->select([
                'item_id' => 'Lote.item_id',
                'Item.nome',
                'quantidade_total' => 'SUM(Lote.quantidade)',
                'Item.unidade',
                'valor_unitario' => 'Lote.valor_unitario'
            ])
            ->contain(['Item'])
            ->groupBy([
                'Lote.item_id',
                'Lote.valor_unitario',
                'Item.nome',
                'Item.unidade'
            ]);

        if (!is_null($tipo_item)) {
            $loteQuery->where(['Item.tipo_item' => $tipo_item]);
        }
        if (!is_null($unidade)) {
            $loteQuery->where(['LOWER(Item.unidade) LIKE' => '%' . strtolower($unidade) . '%']);
        }

        $estoque = [];
        $valorEstoqueTotal = 0;

        foreach ($loteQuery as $row) {
            $quantidade = (float)$row->quantidade_total;
            $valorUnitario = (float)$row->valor_unitario;
            $valorTotal = round($quantidade * $valorUnitario, 2);

            $estoque[] = [
                'item_id' => $row->item_id,
                'item_nome' => $row->item->nome ?? 'Desconhecido',
                'quantidade' => (float)$row->quantidade_total,
                'unidade_medida' => $row->item->unidade ?? '',
                'valor_unitario' => (float)$row->valor_unitario,
                'valor_total' => round((float)$row->quantidade_total * (float)$row->valor_unitario, 2)
            ];

            $valorEstoqueTotal += $valorTotal;
        }

        // Inicializa todas as categorias com zero
        $somaPorTipo = [
            'material' => 0,
            'farmacologico' => 0,
            'medicamento_vet' => 0
        ];

        $itemQuery = $this->itemTable->find()
            ->select([
                'tipo_item' => 'Item.tipo_item',
                'quantidade' => 'COUNT(*)'
            ])
            ->groupBy(['tipo_item']);

        if (!is_null($tipo_item)) {
            $itemQuery->where(['Item.tipo_item' => $tipo_item]);
        }
        if (!is_null($unidade)) {
            $itemQuery->where(['Item.unidade' => $unidade]);
        }

        foreach ($itemQuery as $row) {
            $tipo = $row->tipo_item;
            if (isset($somaPorTipo[$tipo])) {
                $somaPorTipo[$tipo] = (int)$row->quantidade;
            }
        }

        return [
            'itens' => $estoque,
            'valor_total_estoque' => round($valorEstoqueTotal, 2),
            'soma_por_tipo' => $somaPorTipo
        ];
    }

    /**
     * Converte uma string no formato d-m-Y para FrozenDate de forma segura.
     *
     * @param string|null $data
     * @return FrozenDate|null
     */
    function dmyToFrozen(?string $data): ?FrozenDate
    {
        if (empty($data)) {
            return null;
        }

        $obj = DateTime::createFromFormat('d-m-Y', $data);

        if ($obj === false) {
            // Loga erro se formato for inválido
            Log::warning("Data inválida recebida: '$data' — formato esperado: d-m-Y");
            return null;
        }

        return new FrozenDate($obj);
    }

    /**
     * Retorna um relatório das movimentações de entrada com base nos filtros fornecidos.
     *
     * @param array $filtros
     * @return array
     */
    public function getRelatorioEntradas(array $filtros = []): array
    {
        $query = $this->movimentacaoTable->find()
            ->contain(['LotesMovimentacoes.Lote.Item', 'Fornecedor'])
            ->where(['Movimentacao.tipo_movimentacao' => 'entrada'])
            ->orderByAsc('Movimentacao.data');

        if (!empty($filtros['data_inicio'])) {
            $query->where(['Movimentacao.data >=' => $this->dmyToFrozen($filtros['data_inicio'])]);
        }

        if (!empty($filtros['data_fim'])) {
            $query->where(['Movimentacao.data <=' => $this->dmyToFrozen($filtros['data_fim'])]);
        }

        if (!empty($filtros['tipo_entrada'])) {
            $query->where(['Movimentacao.subtipo_movimentacao ILIKE' => '%' . $filtros['tipo_entrada'] . '%']);
        }

        if (!empty($filtros['categoria']) || !empty($filtros['nome_item'])) {
            $query->matching('LotesMovimentacoes.Lote.Item', function ($q) use ($filtros) {
                if (!empty($filtros['categoria'])) {
                    $q->where(['Item.tipo_item' => $filtros['categoria']]);
                }
                if (!empty($filtros['nome_item'])) {
                    $q->where(['Item.nome ILIKE' => '%' . $filtros['nome_item'] . '%']);
                }
                return $q;
            });
        }

        $result = [];


        foreach ($query->all() as $mov) {
            foreach ($mov->lotes_movimentacoes as $loteMov) {
                $item = $loteMov->lote->item;
                if ($item) {
                    $result[] = [
                        'nome_item'          => $item->nome,
                        'quantidade_entrada' => $loteMov->quantidade,
                        'data_entrada'       => $mov->data->format('d-m-Y'),
                        'tipo_entrada'       => $mov->subtipo_movimentacao ?? 'não informado',
                        'documento_origem'   => $mov->documento_origem,
                        'lote'               => $loteMov->lote->numero_lote,
                        'validade'           => $loteMov->lote->data_vencimento->format('d-m-Y'),
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Retorna um relatório das movimentações de saída com base nos filtros fornecidos.
     *
     * @param array $filtros
     * @return array
     */
    public function getRelatorioSaidas(array $filtros = []): array
    {
        $query = $this->movimentacaoTable->find()
            ->contain(['LotesMovimentacoes.Lote.Item', 'Setor'])
            ->where(['Movimentacao.tipo_movimentacao' => 'saida'])
            ->orderByAsc('Movimentacao.data');

        if (!empty($filtros['data_inicio'])) {
            $query->where(['Movimentacao.data >=' => $this->dmyToFrozen($filtros['data_inicio'])]);
        }

        if (!empty($filtros['data_fim'])) {
            $query->where(['Movimentacao.data <=' => $this->dmyToFrozen($filtros['data_fim'])]);
        }

        if (!empty($filtros['tipo_saida'])) {
            $query->where(['Movimentacao.subtipo_movimentacao ILIKE' => '%' . $filtros['tipo_saida'] . '%']);
        }

        if (!empty($filtros['setor_id'])) {
            $query->where(['Movimentacao.setor_id' => $filtros['setor_id']]);
        }

        if (!empty($filtros['ficha_clinica'])) {
            $query->where(['Movimentacao.ficha_clinica ILIKE' => '%' . $filtros['ficha_clinica'] . '%']);
        }

        if (!empty($filtros['categoria']) || !empty($filtros['nome_item'])) {
            $query->matching('LotesMovimentacoes.Lote.Item', function ($q) use ($filtros) {
                if (!empty($filtros['categoria'])) {
                    $q->where(['Item.tipo_item' => $filtros['categoria']]);
                }
                if (!empty($filtros['nome_item'])) {
                    $q->where(['Item.nome ILIKE' => '%' . $filtros['nome_item'] . '%']);
                }
                return $q;
            });
        }

        $result = [];

        foreach ($query->all() as $mov) {
            foreach ($mov->lotes_movimentacoes as $loteMov) {
                $lote = $loteMov->lote;
                $item = $lote->item;
                $total = $loteMov->quantidade * $lote->valor_unitario;

                if ($item) {
                    $result[] = [
                        'nome_item'           => $item->nome,
                        'quantidade_retirada' => $loteMov->quantidade,
                        'ficha_clinica'       => $mov->ficha_clinica,
                        'data_saida'          => $mov->data->format('d-m-Y'),
                        'setor'               => $mov->setor ? $mov->setor->nome : null,
                        'valor_total'         => $total,
                        'valor_unitario'      => $lote->valor_unitario
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Gera estatísticas de consumo com base nas movimentações do tipo 'saida' e subtipo 'saida_item'.
     *
     * @param array $filtros [
     *   'periodo_inicio' => '2024-01-01',
     *   'periodo_fim' => '2024-12-31',
     *   'tipo_item' => 'medicamento_vet',
     *   'unidade' => 'ml',
     *   'completo' => true
     * ]
     * @return array
     * @throws DateMalformedStringException
     */
    public function getEstatisticasConsumo(array $filtros = []): array
    {
        $inicio = $filtros['periodo_inicio'] ?? null;
        $fim = $filtros['periodo_fim'] ?? null;
        $tipoItem = $filtros['tipo_item'] ?? null;
        $unidade = $filtros['unidade'] ?? null;
        $completo = $filtros['completo'] ?? false;

        // 1. Busca o consumo no período
        $query = $this->lotesMovimentacoesTable->find()
            ->select([
                'item_id' => 'Lote.item_id',
                'nome' => 'Item.nome',
                'quantidade_consumida' => 'SUM(LotesMovimentacoes.quantidade)',
                'estoque' => 'SUM(Lote.quantidade)',
                'unidade' => 'Item.unidade',
                'descricao_completa' => 'Item.descricao_completa',
                'codigo_catmat' => 'Catmat.codigo_catmat',
            ])
            ->innerJoinWith('Movimentacao', function ($q) use ($inicio, $fim) {
                return $q
                    ->where([
                        'Movimentacao.tipo_movimentacao' => 'saida',
                        'subtipo_movimentacao IN' => ['consumo famez', 'emprestimo'],
                    ])
                    ->andWhere(function ($exp) use ($inicio, $fim) {
                        $condicoes = [];
                        if ($inicio) {
                            $condicoes[] = $exp->gte('Movimentacao.data', $inicio);
                        }
                        if ($fim) {
                            $condicoes[] = $exp->lte('Movimentacao.data', $fim);
                        }
                        return $exp->and($condicoes);
                    });
            })
            ->leftJoinWith('Lote.Item.Catmat') // ← ESSA LINHA ADICIONADA
            ->groupBy([
                'Lote.item_id',
                'Item.nome',
                'Item.unidade',
                'Item.descricao_completa',
                'Catmat.codigo_catmat',
            ])
            ->orderByAsc('Item.nome');

        if ($tipoItem) {
            $query->where(['Item.tipo_item' => $tipoItem]);
        }

        if ($unidade) {
            $query->where(['LOWER(Item.unidade) LIKE' => '%' . strtolower($unidade) . '%']);
        }

        $estatisticas = [];
        $max = null;
        $min = null;

        foreach ($query as $row) {
            $mediaConsumoDiario = $row->quantidade_consumida / max(1, $this->getDiasNoPeriodo($inicio, $fim));
            $tempoEstimadoDias = $mediaConsumoDiario > 0
                ? (int) floor($row->estoque / $mediaConsumoDiario)
                : null;

            $dados = [
                'item_id' => $row->item_id,
                'nome' => $row->nome,
                'quantidade_consumida' => (int)$row->quantidade_consumida,
                'estoque_atual' => (int)$row->estoque,
                'tempo_estimado_uso_dias' => $tempoEstimadoDias
            ];

            if ($completo) {
                $dados += [
                    'codigo_catmat' => $row->codigo_catmat,
                    'descricao_completa' => $row->descricao_completa,
                    'unidade' => $row->unidade,
                ];
            }

            $estatisticas[] = $dados;

            if (is_null($max) || $row->quantidade_consumida > $max['quantidade']) {
                $max = ['nome' => $row->nome, 'quantidade' => $row->quantidade_consumida];
            }

            if (is_null($min) || $row->quantidade_consumida < $min['quantidade']) {
                $min = ['nome' => $row->nome, 'quantidade' => $row->quantidade_consumida];
            }
        }

        return [
            'itens' => $estatisticas,
            'maior_consumo' => $max,
            'menor_consumo' => $min,
        ];
    }

    /**
     * Retorna o número de dias entre duas datas no formato Y-m-d
     * @throws DateMalformedStringException
     */
    private function getDiasNoPeriodo(?string $inicio, ?string $fim): int
    {
        if (!$inicio || !$fim) {
            return 1; // evita divisão por zero
        }
        $start = new DateTime($inicio);
        $end = new DateTime($fim);
        return max(1, $start->diff($end)->days);
    }

    /**
     * Gera relatório analítico de consumo item a item mês a mês,
     * chamando a função original getEstatisticasConsumo() para cada intervalo mensal.
     *
     * @param array $filtros [
     *   'periodo_inicio' => '2024-01-01',
     *   'periodo_fim' => '2024-06-30',
     *   'tipo_item' => 'medicamento_vet',
     *   'unidade' => 'ml',
     *   'completo' => true
     * ]
     * @return array<string, array>
     * @throws DateMalformedStringException
     */
    public function getEstatisticasConsumoMensal(array $filtros): array
    {
        $inicioOriginal = new DateTimeImmutable($filtros['periodo_inicio']);
        $fimOriginal = new DateTimeImmutable($filtros['periodo_fim']);
        $resultados = [];

        $inicioMes = $inicioOriginal->modify('first day of this month');

        while ($inicioMes <= $fimOriginal) {
            $fimMes = $inicioMes->modify('last day of this month');

            // Limita os intervalos para não extrapolar os limites reais
            $inicioPeriodo = $inicioMes < $inicioOriginal ? $inicioOriginal : $inicioMes;
            $fimPeriodo = $fimMes > $fimOriginal ? $fimOriginal : $fimMes;

            $filtrosMes = [
                'tipo_item' => $filtros['tipo_item'] ?? null,
                'unidade' => $filtros['unidade'] ?? null,
                'completo' => $filtros['completo']
            ];
            $filtrosMes['periodo_inicio'] = $inicioPeriodo->format('Y-m-d');
            $filtrosMes['periodo_fim'] = $fimPeriodo->format('Y-m-d');

            $chaveMes = $inicioMes->format('Y-m');

            $resultadoMes = $this->getEstatisticasConsumo($filtrosMes);
            $resultados[$chaveMes] = $resultadoMes;

            $inicioMes = $inicioMes->modify('first day of next month');
        }

        return $resultados;
    }

    /**
     * Gera, em formato Excel, relatório analítico de consumo ‘item’ a ‘item’, mês a mês.
     *
     * @param array $filtros [
     * 'periodo_inicio' => '2024-01-01',
     * 'periodo_fim' => '2024-06-30',
     * 'tipo_item' => 'medicamento_vet',
     * 'unidade' => 'ml',
     * 'completo' => true
     * ]
     *
     * @throws DateMalformedStringException
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportarEstatisticasConsumoMensalParaExcel(array $filtros): string
    {
        $dadosPorMes = $this->getEstatisticasConsumoMensal($filtros);

        foreach ($dadosPorMes as $mes => $resultadoMes) {
            
            if (isset($resultadoMes['itens']) && is_array($resultadoMes['itens'])) {
                $itens = $resultadoMes['itens'];
            }
            
            elseif (array_keys($resultadoMes) === range(0, count($resultadoMes) - 1)) {
                $itens = $resultadoMes;
            }
            
            else {
                continue;
            }
    
            foreach ($itens as &$item) {
                $item += [
                    'nome'                    => '',
                    'quantidade_consumida'    => 0,
                    'estoque_atual'           => 0,
                    'tempo_estimado_uso_dias' => 0,
                ];
            }
            unset($item);
    
            $dadosPorMes[$mes] = $itens;
        }
    
        return ExcelExporter::gerarPlanilhaComAbas(
            $dadosPorMes,
            fn() => ['Item', 'Quantidade Consumida', 'Estoque Atual', 'Tempo Estimado (dias)'],
            function ($item) {
                return [
                    $item['nome'],
                    $item['quantidade_consumida'],
                    $item['estoque_atual'],
                    $item['tempo_estimado_uso_dias']
                ];
            }
        );
    }
}
