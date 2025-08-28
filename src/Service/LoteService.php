<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Table\LotesMovimentacoesTable;
use App\Model\Table\LoteTable;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\TableRegistry;

class LoteService
{
    private LoteTable $loteTable;
    private ItemService $itemService;
    private LotesMovimentacoesTable $lotesMovimentacoesTable;

    public function __construct()
    {
        $this->loteTable = TableRegistry::getTableLocator()->get('Lote');
        $this->lotesMovimentacoesTable = TableRegistry::getTableLocator()->get('LotesMovimentacoes');
        $this->itemService = new ItemService();
    }

    public function createLote(array $data): array
    {
        $valorUnitario = $data['valor_unitario'];
        $quantidade = $data['quantidade'];
        $data['valor_total'] = $valorUnitario * $quantidade;
        $lotesExistentes = $this->loteTable->find()
            ->where(['item_id' => $data['item_id']])
            ->all();

        $hasActiveLote = false;
        foreach ($lotesExistentes as $loteExistente) {
            if ($loteExistente->is_ativo) {
                $hasActiveLote = true;
                break;
            }
        }

        $novoLote = $this->loteTable->newEmptyEntity();
        $novoLote = $this->loteTable->patchEntity($novoLote, $data);

        if (!$hasActiveLote) {
            $novoLote->is_ativo = true;

        } else {
            $novoLote->is_ativo = false;

        }

        if ($this->loteTable->save($novoLote)) {
            $this->atualizarFilaDeLote();

            $novoLote = $this->loteTable->get($novoLote->id);

            return [
                'success' => true,
                'message' => 'O lote foi salvo com sucesso.',
                'data' => $novoLote
            ];
        }

        return [
            'success' => false,
            'message' => 'O lote não pôde ser salvo, tente novamente.',
            'errors' => $novoLote->getErrors()
        ];
    }

    public function getLotesByItemId(int $itemId)
    {
        return $this->loteTable->find('all')
            ->where(['item_id' => $itemId, 'is_ativo' => true]);
    }

    public function getLoteById(int $loteId)
    {
        return $this->loteTable->get($loteId);
    }

    public function atualizarFilaDeLote(): void
    {
        $lotes = $this->loteTable->find()
            ->where(['data_vencimento >=' => date('Y-m-d')])
            ->order(['data_vencimento' => 'ASC', 'id' => 'ASC'])
            ->all();

        if ($lotes->isEmpty()) {
            return;
        }

        $lotesPorItem = [];
        foreach ($lotes as $lote) {
            $lotesPorItem[$lote->item_id][] = $lote;
        }

        foreach ($lotesPorItem as $itemId => $lotesItem) {
            $loteAtivo = null;

            foreach ($lotesItem as $lote) {
                if ($lote->quantidade <= 0) {
                    $lote->is_ativo = false;
                } else {
                    if ($loteAtivo === null) {
                        $lote->is_ativo = true;
                        $loteAtivo = $lote;
                    } else {
                        $lote->is_ativo = false;
                    }
                }
                $this->loteTable->save($lote);
            }
        }
    }
    public function descontarQuantidadeDoLoteAtivo(int $itemId, int $quantidade): array
    {
        $resultados = [];
        $lotes = $this->loteTable->find('all')
            ->where(['item_id' => $itemId, 'is_ativo' => true])
            ->order(['data_vencimento' => 'ASC', 'id' => 'ASC'])
            ->all();

        foreach ($lotes as $lote) {
            if ($quantidade <= 0) {
                break;
            }

            $quantidadeOriginal = $lote->quantidade;
            if ($lote->quantidade >= $quantidade) {
                $lote->quantidade -= $quantidade;
                $quantidadeDebitada = $quantidadeOriginal - $lote->quantidade;
                $quantidade = 0;
            } else {
                $quantidadeDebitada = $lote->quantidade;
                $quantidade -= $lote->quantidade;
                $lote->quantidade = 0;
            }

            $lote->is_ativo = ($lote->quantidade > 0);

            $this->loteTable->save($lote);

            $resultados[] = [
                'id_lote' => $lote->id,
                'quantidade_debitada' => $quantidadeDebitada
            ];
        }

        $this->atualizarFilaDeLote();
        if ($quantidade > 0) {
            return [
                'status' => 'error',
                'message' => 'Quantidade solicitada é maior do que a disponível nos lotes ativos.',
                'data' => $resultados
            ];
        }
        return [
            'status' => 'success',
            'data' => $resultados
        ];
    }

    public function descontarQuantidadeDoLotePerda(int $loteId, int $quantidade, int $movimentacaoId): array
    {
        try {
            if ($loteId === null) {
                return [
                    'status' => 'error',
                    'message' => 'Erro: O ID do lote não pode ser null.',
                ];
            }

            $lote = $this->loteTable->get($loteId);

            if ($quantidade > $lote->quantidade) {
                return [
                    'status' => 'error',
                    'message' => 'Erro: A quantidade a ser descontada é maior do que a quantidade disponível no lote.',
                ];
            }

            $lote->quantidade = max(0, $lote->quantidade - $quantidade);

            if ($lote->quantidade === 0) {
                $lote->is_ativo = false;
            }

            if (!$this->loteTable->save($lote)) {
                return [
                    'status' => 'error',
                    'message' => 'Erro ao salvar as alterações no lote ID ' . $loteId,
                ];
            }

            $loteMovimentacaoEntity = $this->lotesMovimentacoesTable->newEntity([
                'movimentacao_id' => $movimentacaoId,
                'lote_id' => $loteId,
                'quantidade' => $quantidade,
            ]);

            if (!$this->lotesMovimentacoesTable->save($loteMovimentacaoEntity)) {
                return [
                    'success' => false,
                    'message' => 'Erro ao registrar o desconto na movimentação.',
                ];
            }

            return [
                'status' => 'success',
                'message' => 'Quantidade descontada com sucesso.',
            ];

        } catch (RecordNotFoundException $e) {
            return [
                'status' => 'error',
                'message' => 'Erro: Lote com ID ' . $loteId . ' não foi encontrado.',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erro inesperado: ' . $e->getMessage(),
            ];
        } finally {
            $this->atualizarFilaDeLote();
        }
    }

    public function lotesProximosVencimento(int $dias = 30): array
    {
        $hoje = date('Y-m-d');
        $limite = date('Y-m-d', strtotime("+$dias days"));

        $lotes = $this->loteTable->find()
            ->contain(['Item'])
            ->where([
                'data_vencimento >=' => $hoje,
                'data_vencimento <=' => $limite
            ])
            ->all();

        $payload = [];
        foreach ($lotes as $lote) {
            $payload[] = [
                'item' => $lote->item->nome ?? '',
                'lote' => $lote->numero_lote,
                'validade' => $lote->data_vencimento
            ];
        }

        return $payload;
    }

    public function calcularDebito(int $itemId, int $quantidade): array
    {
        $lotesDebitados = [];

        $lotes = $this->getLotesByItemId($itemId)
            ->order(['data_vencimento' => 'ASC'])
            ->all();

        if ($lotes->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Não existem lotes para o ítem ID ' . $itemId . '.',
                'data' => []
            ];
        }

        $filaLotes = [];
        $loteAtivoEncontrado = false;

        foreach ($lotes as $lote) {
            if ($lote->is_ativo) {
                $loteAtivoEncontrado = true;
            }
            if ($loteAtivoEncontrado && $lote->quantidade > 0) {
                $filaLotes[] = $lote;
            }
        }

        if (empty($filaLotes)) {
            return [
                'success' => false,
                'message' => 'Não há lotes ativos disponíveis para cálculo no ítem ID ' . $itemId . '.',
                'data' => []
            ];
        }

        foreach ($filaLotes as $lote) {
            if ($quantidade <= 0) {
                break;
            }

            $quantidadeDebitada = min($lote->quantidade, $quantidade);
            $lotesDebitados[] = [
                'numero_lote' => $lote->numero_lote,
                'quantidade_debitada' => $quantidadeDebitada
            ];
            $quantidade -= $quantidadeDebitada;
        }

        return [
            'success' => true,
            'message' => 'Cálculo de débito realizado com sucesso.',
            'data' => $lotesDebitados
        ];
    }

    public function log(int $itemId, int $quantidade): array
    {
        $lotesDebitados = [];

        $lotes = $this->getLotesByItemId($itemId)
            ->order(['data_vencimento' => 'ASC'])
            ->all();

        if ($lotes->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Não existem lotes para o ítem ID ' . $itemId . '.',
                'data' => []
            ];
        }

        $filaLotes = [];
        $loteAtivoEncontrado = false;

        foreach ($lotes as $lote) {
            if ($lote->is_ativo) {
                $loteAtivoEncontrado = true;
            }
            if ($loteAtivoEncontrado && $lote->quantidade > 0) {
                $filaLotes[] = $lote;
            }
        }

        if (empty($filaLotes)) {
            return [
                'success' => false,
                'message' => 'Não há lotes ativos disponíveis para cálculo no ítem ID ' . $itemId . '.',
                'data' => []
            ];
        }

        foreach ($filaLotes as $lote) {
            if ($quantidade <= 0) {
                break;
            }

            if ($lote->quantidade >= $quantidade) {
                $lotesDebitados[] = [
                    'id_lote' => $lote->id,
                    'quantidade_debitada' => $quantidade
                ];

                $quantidade = 0;
            } else {
                $lotesDebitados[] = [
                    'id_lote' => $lote->id,
                    'quantidade_debitada' => $lote->quantidade
                ];

                $quantidade -= $lote->quantidade;
            }
        }

        return [
            'success' => true,
            'message' => 'Cálculo de débito realizado com sucesso.',
            'data' => $lotesDebitados
        ];
    }

    public function verificarEstoqueMinimoParaTodosItens(): array
    {
        $itens = $this->loteTable->Item->find('all')->all();
        $resultados = [];

        foreach ($itens as $item) {
            $quantidadeTotal = $this->getQuantidadeTotalPorItem($item->id);

            if ($quantidadeTotal < $item->estoque_minimo) {
                $resultados[] = [
                    'item_id' => $item->id,
                    'nome' => $item->nome,
                    'estoque_minimo' => $item->estoque_minimo,
                    'estoque_restante' => $quantidadeTotal
                ];
            }
        }

        return [
            'status' => 'warning',
            'message' => 'Itens com estoque abaixo do mínimo.',
            'data' => $resultados
        ];
    }

    public function getQuantidadeTotalPorItem(int $itemId): int
    {
        $lotes = $this->loteTable->find()
            ->where(['item_id' => $itemId])
            ->all();

        $quantidadeTotal = 0;
        foreach ($lotes as $lote) {
            $quantidadeTotal += $lote->quantidade;
        }

        return $quantidadeTotal;
    }

    public function buscarLoteById(int $id): array
    {
        $lote = $this->loteTable->find()
            ->where(['id' => $id])
            ->first();

        if ($lote) {
            return [
                'status' => 'success',
                'data' => [
                    'id' => $lote->id,
                    'data_vencimento' => $lote->data_vencimento,
                    'data_de_recebimento' => $lote->data_de_recebimento,
                    'quantidade' => $lote->quantidade,
                    'valor_unitario' => $lote->valor_unitario,
                    'valor_total' => $lote->valor_total,
                    'item_id' => $lote->item_id,
                    'numero_lote' => $lote->numero_lote,
                    'is_ativo' => $lote->is_ativo,
                    'documento_de_origem' => $lote->documento_de_origem,
                    'fornecedor_id' => $lote->fornecedor_id,
                ]
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Ítem não encontrado.'
            ];
        }
    }

    public function getValorTotalDeTodosOsLotes(): float
    {
        $lotes = $this->loteTable->find()->all();

        $valorTotal = 0.0;
        foreach ($lotes as $lote) {
            if ($lote->quantidade > 0) {
                $valorTotal += $lote->valor_total;
            }
        }

        return $valorTotal;
    }
    public function editLote(int $id, array $data): array
    {
        try {
            $lote = $this->loteTable->get($id);

            $lote = $this->loteTable->patchEntity($lote, $data);

            if (isset($data['valor_unitario']) || isset($data['quantidade'])) {
                $valorUnitario = $data['valor_unitario'] ?? $lote->valor_unitario;
                $quantidade = $data['quantidade'] ?? $lote->quantidade;
                $lote->valor_total = $valorUnitario * $quantidade;
            }

            $dataRecebimento = $data['data_de_recebimento'] ?? $lote->data_de_recebimento;
            $dataVencimento = $data['data_vencimento'] ?? $lote->data_vencimento;
            if ($dataVencimento < $dataRecebimento) {
                return [
                    'success' => false,
                    'message' => 'A data de vencimento não pode ser menor que a data de recebimento.'
                ];
            }

            if ($this->loteTable->save($lote)) {
                $this->atualizarFilaDeLote();
                return [
                    'success' => true,
                    'message' => 'Lote atualizado com sucesso.',
                    'data' => $lote
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao atualizar o lote.',
                    'errors' => $lote->getErrors()
                ];
            }
        } catch (RecordNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Lote não encontrado.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro inesperado: ' . $e->getMessage()
            ];
        }
    }
}