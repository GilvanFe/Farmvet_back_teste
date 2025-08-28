<?php

namespace App\Service;

use Cake\ORM\TableRegistry;
use App\Service\LoteService;
use App\Model\Table\ItemTable;
use App\Model\Table\LotesMovimentacoesTable;
use App\Model\Table\LoteTable;
use App\Model\Table\MovimentacaoTable;
use Cake\Datasource\ConnectionManager;
use Cake\Log\Log;
use Cake\Utility\Text;

class MovimentacaoService
{
    private MovimentacaoTable $movimentacaoTable;
    private LoteService $loteService;
    private LotesMovimentacoesTable $loteMovimentacoesTable;
    private LoteTable $loteTable;
    private ItemTable $itemTable;

    public function __construct()
    {
        $this->movimentacaoTable = TableRegistry::getTableLocator()->get('Movimentacao');;
        $this->loteService = new LoteService();

        $this->loteMovimentacoesTable = TableRegistry::getTableLocator()->get('LotesMovimentacoes');
        $this->loteTable = TableRegistry::getTableLocator()->get('Lote');
        $this->itemTable = TableRegistry::getTableLocator()->get('Item');
    }

    /**
     * Cria uma movimentação.
     * Valida campos comuns e encaminha para função adequada.
     *
     * @param array $data
     * @return array
     */
    public function registrarMovimentacao(array $data): array {
        $tipo = $data['tipo_movimentacao'] ?? null;
        $subtipo = $data['subtipo_movimentacao'] ?? null;

        Log::write(1, 'Payload recebido: ' . json_encode($data));

        if (!$tipo || !$subtipo) {
            return ['success' => false, 'message' => 'Tipo e subtipo são obrigatórios.'];
        }

        return match (true) {
            $tipo === 'saida' && $subtipo === 'perda' => $this->registrarPerda($data),
            $tipo === 'saida' && $subtipo === 'vencimento' => $this->registrarVencimento($data),
            $tipo === 'saida' && in_array($subtipo, ['consumo famez', 'emprestimo']) => $this->registrarSaidaItem($data),
            $tipo === 'entrada' && in_array($subtipo, ['compra', 'devolucao', 'emprestimo']) => $this->registrarEntrada($data, $subtipo),
            default => ['success' => false, 'message' => 'Tipo ou subtipo não reconhecido.']
        };
    }

    /**
     * Registra uma movimentação de entrada (compra, devolução ou empréstimo).
     *
     * @param array $data Dados da movimentação com lote.
     * @param string $subtipo Subtipo de entrada ('compra', 'devolucao', 'emprestimo')
     * @return array Resultado da operação
     */
    private function registrarEntrada(array $data, string $subtipo): array {
        $camposObrigatorios = ['data', 'fornecedor_id', 'via_compra', 'documento_origem', 'lote'];
        foreach ($camposObrigatorios as $campo) {
            if (empty($data[$campo])) {
                return ['success' => false, 'message' => "Campo obrigatório '$campo' não informado."];
            }
        }

        $conn = ConnectionManager::get('default');
        $conn->begin();

        try {
            $movimentacaoEntity = $this->movimentacaoTable->newEntity([
                'tipo_movimentacao' => 'entrada',
                'subtipo_movimentacao' => $subtipo,
                'data' => $data['data'],
                'fornecedor_id' => !empty($data['fornecedor_id']) ? (int)$data['fornecedor_id'] : null,
                'via_compra' => $data['via_compra'] ?? null,
                'documento_origem' => $data['documento_origem'] ?? null,
                'item_id' => $data['item_id'] ?? null,
            ], ['accessibleFields' => ['fornecedor_id' => true, 'via_compra' => true, 'documento_origem' => true]]);

            if (!$this->movimentacaoTable->save($movimentacaoEntity)) {
                $conn->rollback();
                return ['success' => false, 'message' => 'Erro ao salvar movimentação.', 'errors' => $movimentacaoEntity->getErrors()];
            }

            foreach ($data['lote'] as $loteData) {
                $loteExistente = $this->loteTable->find()
                    ->where([
                        'item_id' => $loteData['item_id'],
                        'numero_lote' => $loteData['numero_lote']
                    ])
                    ->first();

                if ($loteExistente) {
                    $loteExistente->quantidade += $loteData['quantidade'];
                    if($loteExistente->is_ativo === false) {
                        $loteExistente->is_ativo = true;
                    }
                    if (!$this->loteTable->save($loteExistente)) {
                        $conn->rollback();
                        return [
                            'success' => false,
                            'message' => 'Erro ao atualizar quantidade do lote existente.',
                            'errors' => $loteExistente->getErrors()
                        ];
                    }

                    $loteMov = $this->loteMovimentacoesTable->newEntity([
                        'movimentacao_id' => $movimentacaoEntity->id,
                        'lote_id' => $loteExistente->id,
                        'quantidade' => $loteData['quantidade']
                    ]);

                    if (!$this->loteMovimentacoesTable->save($loteMov)) {
                        $conn->rollback();
                        return ['success' => false, 'message' => 'Erro ao vincular lote à movimentação.'];
                    }

                    continue;
                }

                $novoLote = $this->loteTable->newEntity($loteData);

                if (!$this->loteTable->save($novoLote)) {
                    $conn->rollback();
                    return ['success' => false, 'message' => 'Erro ao salvar lote de entrada.', 'errors' => $novoLote->getErrors()];
                }

                $loteMov = $this->loteMovimentacoesTable->newEntity([
                    'movimentacao_id' => $movimentacaoEntity->id,
                    'lote_id' => $novoLote->id,
                    'quantidade' => $novoLote->quantidade
                ]);

                if (!$this->loteMovimentacoesTable->save($loteMov)) {
                    $conn->rollback();
                    return ['success' => false, 'message' => 'Erro ao vincular lote à movimentação.'];
                }
            }

            $conn->commit();
            return ['success' => true, 'message' => 'Movimentação de entrada registrada com sucesso.', 'data' => $movimentacaoEntity];
        } catch (\Exception $e) {
            $conn->rollback();
            return ['success' => false, 'message' => 'Erro inesperado: ' . $e->getMessage()];
        }
    }

    /**
     * Registra uma movimentação de saída por perda de item.
     *
     * @param array $data Dados da movimentação e lote afetados
     * @return array Resultado da operação
     */
    private function registrarPerda(array $data): array
    {
        $camposObrigatorios = ['data', 'lote'];
        foreach ($camposObrigatorios as $campo) {
            if (empty($data[$campo])) {
                return ['success' => false, 'message' => "Campo obrigatório '$campo' não informado."];
            }
        }

        $conn = ConnectionManager::get('default');
        $conn->begin();

        try {
            $mov = $this->movimentacaoTable->newEntity([
                'tipo_movimentacao' => 'saida',
                'subtipo_movimentacao' => 'perda',
                'data' => $data['data'],
                'observacao' => $data['observacao']
            ]);

            if (!$this->movimentacaoTable->save($mov)) {
                $conn->rollback();
                return ['success' => false, 'message' => 'Erro ao salvar movimentação.'];
            }

            foreach ($data['lote'] as $loteData) {
                $lote = $this->loteTable->get($loteData['lote_id']);

                if ($lote->is_ativo === false) {
                    $conn->rollback();
                    return ['success' => false, 'message' => 'Lote já está inativo.'];
                }

                $qtdPerdida = $loteData['quantidade'];

                $lote->quantidade -= $qtdPerdida;
                if ($lote->quantidade === 0) $lote->is_ativo = false;

                if (!$this->loteTable->save($lote)) {
                    $conn->rollback();
                    return ['success' => false, 'message' => 'Erro ao atualizar lote.'];
                }

                $this->loteMovimentacoesTable->save(
                    $this->loteMovimentacoesTable->newEntity([
                        'movimentacao_id' => $mov->id,
                        'lote_id' => $lote->id,
                        'quantidade' => $qtdPerdida
                    ])
                );
            }

            $conn->commit();
            return ['success' => true, 'message' => 'Movimentação de perda registrada.', 'data' => $mov];
        } catch (\Exception $e) {
            $conn->rollback();
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    /**
     * Retorna todas as movimentações relacionadas a um determinado lote.
     *
     * @param int $movimentacaoId
     * @return array
     */
    public function getMovimentacoesByLoteId(int $movimentacaoId): array
    {
        $movimentacoes = $this->movimentacaoTable->find()
            ->matching('LotesMovimentacoes', function ($q) use ($movimentacaoId) {
            return $q->where(['LotesMovimentacoes.movimentacao_id' => $movimentacaoId]);
            })
            ->contain([
            'LotesMovimentacoes.Lote'
            ])
            ->distinct(['Movimentacao.id'])
            ->toArray();

        $result = [];
        foreach ($movimentacoes as $movimentacao) {
            if (isset($movimentacao->lotes_movimentacoes)) {
                foreach ($movimentacao->lotes_movimentacoes as $lm) {
                    if (isset($lm->lote)) {
                        $result[] = [
                            'movimentacao_id' => $movimentacao->id,
                            'tipo_movimentacao' => $movimentacao->tipo_movimentacao,
                            'subtipo_movimentacao' => $movimentacao->subtipo_movimentacao,
                            'data' => $movimentacao->data,
                            'quantidade' => $lm->quantidade,
                            'lote' => $lm->lote,
                        ];
                    }
                }
            }
        }
        
            if (empty($result)) {
        $todasMovimentacoes = $this->movimentacaoTable->find()
            ->contain([
                'LotesMovimentacoes' => function ($q) {
                    return $q->contain(['Lote']); // Limita a 5 lotes por movimentação para não sobrecarregar
                },
                'Setor',
                'Fornecedor'
            ]) // Limita a 20 movimentações no total
            ->toArray();

        foreach ($todasMovimentacoes as $movimentacao) {
            foreach ($movimentacao->lotes_movimentacoes as $lm) {
                $result[] = [
                    'movimentacao_id' => $movimentacao->id,
                    'tipo_movimentacao' => $movimentacao->tipo_movimentacao,
                    'subtipo_movimentacao' => $movimentacao->subtipo_movimentacao,
                    'data' => $movimentacao->data,
                    'quantidade' => $lm->quantidade,
                    'lote' => $lm->lote,
                    'setor' => $movimentacao->setor->nome ?? null,
                    'fornecedor' => $movimentacao->fornecedor->nome ?? null,
                    'is_related' => false // Indica que não está diretamente relacionada ao lote buscado
                ];
            }
        }
        }
        return $result;
    }

    /**
     * Registra uma movimentação de saída por vencimento de item.
     *
     * @param array $data Dados da movimentação e lote vencidos
     * @return array Resultado da operação
     */
    private function registrarVencimento(array $data): array {
        $camposObrigatorios = ['data', 'lote'];
        foreach ($camposObrigatorios as $campo) {
            if (empty($data[$campo])) {
                return ['success' => false, 'message' => "Campo obrigatório '$campo' não informado."];
            }
        }

        $conn = ConnectionManager::get('default');
        $conn->begin();

        try {
            $mov = $this->movimentacaoTable->newEntity([
                'tipo_movimentacao' => 'saida',
                'subtipo_movimentacao' => 'vencimento',
                'data' => $data['data'],
                'observacao' => $data['observacao']
            ]);

            if (!$this->movimentacaoTable->save($mov)) {
                $conn->rollback();
                return ['success' => false, 'message' => 'Erro ao salvar movimentação.'];
            }

            foreach ($data['lote'] as $loteData) {
                $lote = $this->loteTable->get($loteData['lote_id']);

                if ($lote->is_ativo === false) {
                    $conn->rollback();
                    return ['success' => false, 'message' => 'Lote já está inativo.'];
                }

                $qtdVencida = $lote->quantidade;

                $lote->quantidade -= $qtdVencida;
                if ($lote->quantidade === 0) $lote->is_ativo = false;

                if (!$this->loteTable->save($lote)) {
                    $conn->rollback();
                    return ['success' => false, 'message' => 'Erro ao atualizar lote.'];
                }

                $this->loteMovimentacoesTable->save(
                    $this->loteMovimentacoesTable->newEntity([
                        'movimentacao_id' => $mov->id,
                        'lote_id' => $lote->id,
                        'quantidade' => $qtdVencida
                    ])
                );
            }

            $conn->commit();
            return ['success' => true, 'message' => 'Movimentação de vencimento registrada.', 'data' => $mov];
        } catch (\Exception $e) {
            $conn->rollback();
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    /**
     * Registra uma movimentação de saída de item para uso (ex: atendimento clínico).
     *
     * @param array $data Dados da movimentação e uso clínico
     * @return array Resultado da operação
     */
    private function registrarSaidaItem(array $data): array {
        $camposObrigatorios = ['data', 'nome_animal', 'setor', 'requerimento', 'ficha_clinica', 'requerente'];
        foreach ($camposObrigatorios as $campo) {
            if (empty($data[$campo])) {
                return ['success' => false, 'message' => "Campo obrigatório '$campo' não informado."];
            }
        }

        $conn = ConnectionManager::get('default');
        $conn->begin();

        try {
            $mov = $this->movimentacaoTable->newEntity([
                'tipo_movimentacao' => 'saida',
                'subtipo_movimentacao' => $data['subtipo_movimentacao'] ?? 'consumo famez',
                'data' => $data['data'],
                'nome_animal' => $data['nome_animal'],
                'setor_id' => $data['setor'],
                'requerimento' => $data['requerimento'],
                'ficha_clinica' => $data['ficha_clinica'],
                'requerente' => $data['requerente'],
                'observacao' => $data['observacao'] ?? null
            ]);

            if (!$this->movimentacaoTable->save($mov)) {
                $conn->rollback();
                return ['success' => false, 'message' => 'Erro ao salvar movimentação.', 'error'=> $mov->getErrors()];
            }

            foreach ($data['lote'] as $loteData) {

                $loteInfo = $this->loteService->descontarQuantidadeDoLoteAtivo($loteData['item_id'], $loteData['quantidade']);
                
                foreach ($loteInfo['data'] as $lote) {
                    $this->loteMovimentacoesTable->save(
                        $this->loteMovimentacoesTable->newEntity([
                            'movimentacao_id' => $mov->id,
                            'lote_id' => $lote['id_lote'],
                            'quantidade' => $lote['quantidade_debitada']
                        ])
                    );
                }
            }

            $conn->commit();
            return ['success' => true, 'message' => 'Movimentação de saída registrada.'];
        } catch (\Exception $e) {
            $conn->rollback();
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    public function searchMovimentacaoSaida(string $searchTerm): array
    {
        $query = $this->movimentacaoTable->find('all')
        ->contain(['Setor'])
        ->where(['subtipo_movimentacao IN' => ['consumo famez', 'emprestimo', 'saida_item']]);
        if (!empty($searchTerm)) {
            $query->where(['LOWER(Setor.nome) LIKE' => '%' . strtolower($searchTerm) . '%']);
        }
        return $query->toArray();
    }

    public function searchMovimentacaoVencimento(string $searchTerm): array
{
    $query = $this->movimentacaoTable->find('all')
        ->where(['subtipo_movimentacao' => 'vencimento']); 

    if (!empty($searchTerm)) {
        $query->where([
            'LOWER(CAST(Lote.numero_lote AS TEXT)) LIKE' => '%' . strtolower($searchTerm) . '%'
        ]);
    }

    return $query->toArray();
}

    public function searchMovimentacaoPerda(string $searchTerm): array
{
    $query = $this->movimentacaoTable->find('all')
        ->where(['subtipo_movimentacao' => 'perda']); 

    if (!empty($searchTerm)) {
        $query->where([
            'LOWER(CAST(Lote.numero_lote AS TEXT)) LIKE' => '%' . strtolower($searchTerm) . '%'
        ]);
    }

    return $query->toArray();
}

    public function searchMovimentacaoEntrada($searchTerm)
    {
        $query = $this->movimentacaoTable->find('all')
            ->contain(['Fornecedor'])
            ->where(['tipo_movimentacao' => 'entrada']);

        if (!empty($searchTerm)) {
            $query->where(['CAST(Movimentacao.id AS TEXT) LIKE' => '%' . strtolower($searchTerm) . '%']);
        }

        $result = $query->toArray();
        foreach ($result as &$movimentacao) {
            if (isset($movimentacao->fornecedor_id) && empty($movimentacao->fornecedor)) {
                $fornecedorTable = TableRegistry::getTableLocator()->get('Fornecedor');
                $movimentacao->fornecedor = $fornecedorTable->find()->where(['fornecedor_id' => $movimentacao->fornecedor_id])->first();
            }
        }
        return $result;
    }
    /**
     * Obtém os detalhes de uma movimentação por ID, incluindo setor, fornecedor e lotes relacionados.
     *
     * @param int $id ID da movimentação
     * @return array|null
     */
    public function getMovimentacaoDetalhadaById(int $id): ?array
    {
        $mov = $this->movimentacaoTable->find()
            ->where(['Movimentacao.id' => $id])
            ->contain([
            'Setor' => ['joinType' => 'LEFT'],
            'Fornecedor' => ['joinType' => 'LEFT'],
            'LotesMovimentacoes.Lote' => ['joinType' => 'LEFT'],
            ])
            ->first();

        if (!$mov) {
            return null;
        }

        $lotes = [];
        if (!empty($mov->lotes_movimentacoes)) {
            foreach ($mov->lotes_movimentacoes as $lm) {
                if (!empty($lm->lote)) {
                    $lotes[] = $lm->lote;
                }
            }
        }

        return [
            'id' => $mov->id,
            'tipo_movimentacao' => $mov->tipo_movimentacao,
            'subtipo_movimentacao' => $mov->subtipo_movimentacao,
            'data' => $mov->data,
            'setor' => $mov->setor ?? null,
            'fornecedor' => $mov->fornecedor ?? null,
            'lotes' => $lotes,
        ];
    }


}