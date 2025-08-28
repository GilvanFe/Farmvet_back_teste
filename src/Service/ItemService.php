<?php

namespace App\Service;

use App\Model\Table\CatmatTable;
use App\Model\Table\ItemTable;
use Cake\ORM\TableRegistry;
use Cake\Log\Log;
use Cake\I18n\FrozenTime;

class ItemService
{
    protected ItemTable $itemTable;
    protected CatmatTable $catmatTable;

    public function __construct()
    {
        $this->itemTable = TableRegistry::getTableLocator()->get('Item');
        $this->catmatTable = TableRegistry::getTableLocator()->get('Catmat');
    }
    public function updateItemAndMaybeAddCatmat($id, $data): array
    {
        $item = $this->itemTable->get($id);

        if (!$item) {
            return ['status' => 'error', 'message' => 'Item não encontrado.'];
        }

        $item = $this->itemTable->patchEntity($item, $data);

        $novoCodigoCatmat = $data['codigo_catmat'] ?? null;

        if ($novoCodigoCatmat !== null) {
            $ultimoCatmat = $this->catmatTable->find()
                ->where(['item_id' => $id])
                ->order(['data' => 'DESC'])
                ->first();

            $novoCatmat = $this->catmatTable->newEntity([
                'codigo_catmat' => $novoCodigoCatmat,
                'item_id' => $id,
                'data' => new \DateTime('now')
            ]);

                if (!$this->catmatTable->save($novoCatmat)) {
                    $dataAtual = (new \DateTime())->format('Y-m-d H:i:s');

                    $ultimoCodigo = $ultimoCatmat ? $ultimoCatmat->codigo_catmat : null;

                    return [
                        'status' => 'error',
                        'message' => 'Item atualizado, mas falha ao salvar novo Catmat.',
                        'data' => [
                            'codigo_atual' => $ultimoCodigo,
                            'novo_codigo_tentado' => $novoCodigoCatmat,
                            'item_id' => $id,
                            'data_tentativa' => $dataAtual,
                            'errors' => $novoCodigoCatmat->getErrors(),
                            'data_recebida' => $data,
                        ]
                    ];
                }
        }

        if ($this->itemTable->save($item)) {
            return ['status' => 'success', 'message' => 'Item e Catmat atualizados com sucesso.', 'data' => $item, 'catmat' => $novoCatmat ?? null];
        } else {
            return ['status' => 'error', 'message' => 'Erro ao atualizar o item.'];
        }
    }


    public function createItemWithCatmat(array $data): array
    {
        $requiredFields = ['nome', 'tipo_item', 'estoque_minimo', 'codigo_catmat', 'unidade'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return ['status' => 'error', 'message' => "Campo obrigatório ausente: $field"];
            }
        }

        try {
            $existingCatmat = $this->catmatTable->find()
                ->where(['codigo_catmat' => $data['codigo_catmat']])
                ->first();

            if ($existingCatmat) {
                return ['status' => 'error', 'message' => 'O código catmat já existe.'];
            }

            $this->itemTable->getConnection()->begin();

            $itemEntity = $this->itemTable->newEntity($data);

            if ($itemEntity->getErrors()) {
                $this->itemTable->getConnection()->rollback();
                return [
                    'status' => 'error',
                    'message' => 'Erro de validação.',
                    'errors' => $itemEntity->getErrors()
                ];
            }

            if ($this->itemTable->save($itemEntity)) {
                $catmatData = [
                    'codigo_catmat' => $data['codigo_catmat'],
                    'item_id' => $itemEntity->id
                ];
                $catmatEntity = $this->catmatTable->newEntity($catmatData);

                if ($this->catmatTable->save($catmatEntity)) {
                    $this->itemTable->getConnection()->commit();
                    return ['status' => 'success', 'message' => 'Item e catmat criados com sucesso.'];
                } else {
                    $this->itemTable->getConnection()->rollback();
                    return ['status' => 'error', 'message' => 'Erro ao salvar o catmat.'];
                }
            } else {
                $this->itemTable->getConnection()->rollback();
                return ['status' => 'error', 'message' => 'Erro ao salvar o item.'];
            }

        } catch (\Exception $e) {
            $this->itemTable->getConnection()->rollback();
            return ['status' => 'error', 'message' => 'Ocorreu um erro: ' . $e->getMessage()];
        }
    }


    public function getItemsUltimoCatmat()
    {
        $items = $this->itemTable->find()->all();

        $result = [];

        $catmatTable = TableRegistry::getTableLocator()->get('Catmat');

        foreach ($items as $item) {

            $latestCatmat = $catmatTable
                ->find()
                ->where(['Catmat.item_id' => $item->id])
                ->orderByDesc('Catmat.data')
                ->first();


            $result[] = [
                'id' => $item->id,
                'nome' => $item->nome,
                'tipo_item' => $item->tipo_item,
                'estoque_minimo' => $item->estoque_minimo,
                'is_ativo' => $item->is_ativo,
                'is_controlado' => $item->is_controlado,
                'descricao_completa' => $item->descricao_completa,
                'descricao_complementar' => $item->descricao_complementar,
                'unidade' => $item->unidade,
                'observacao' => $item->observacao,
                'legislacao_especifica' => $item->legislacao_especifica,
                'codigo_catmat' => $latestCatmat ? $latestCatmat->codigo_catmat : null,
            ];
        }

        return $result;
    }

    public function searchItems(string $searchTerm): array
    {
        $query = $this->itemTable->find('all');
        $query->where(['is_ativo' => true]);

        if (!empty($searchTerm)) {
            $query->where(['LOWER(nome) LIKE' => '%' . strtolower($searchTerm) . '%']);
        }

        return $query->toArray();
    }

    public function getItemById(int $id): array
    {
        $item = $this->itemTable->find()
            ->where(['id' => $id])
            ->first();

        if ($item) {
            $latestCatmat = $this->catmatTable->find()
                ->where(['item_id' => $id])
                ->order(['data' => 'DESC'])
                ->first();

            return [
                'status' => 'success',
                'data' => [
                    'id' => $item->id,
                    'nome' => $item->nome,
                    'tipo_item' => $item->tipo_item,
                    'estoque_minimo' => $item->estoque_minimo,
                    'is_ativo' => $item->is_ativo,
                    'is_controlado' => $item->is_controlado,
                    'descricao_completa' => $item->descricao_completa,
                    'descricao_complementar' => $item->descricao_complementar,
                    'unidade' => $item->unidade,
                    'observacao' => $item->observacao,
                    'legislacao_especifica' => $item->legislacao_especifica,
                    'codigo_catmat' => $latestCatmat ? $latestCatmat->codigo_catmat : null
                ]
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Item não encontrado.'
            ];
        }
    }

    public function buscarPorNome(string $termoBusca): array
    {
        $query = $this->itemTable->find()
            ->select([
                'Item.id',
                'Item.nome',
                'Item.tipo_item',
                'Item.estoque_minimo',
                'Item.is_ativo',
                'Item.is_controlado',
                'Item.descricao_completa',
                'Item.descricao_complementar',
                'Item.unidade',
                'Item.observacao',
                'Item.legislacao_especifica',
                'codigo_catmat' => $this->itemTable->Catmat->find()
                    ->select(['codigo_catmat'])
                    ->where(['Catmat.item_id = Item.id'])
                    ->orderByDesc('Catmat.data')
                    ->limit(1),
                'quantidade_total' => $this->itemTable->find()
                    ->func()
                    ->coalesce([
                        $this->itemTable->find()->func()->sum('Lote.quantidade'),
                        0
                    ]),
            ])
            ->innerJoinWith('Catmat', function ($join) {
                return $join->order(['Catmat.data' => 'DESC'])->limit(1);
            })
            ->leftJoinWith('Lote')
            ->where([
                'OR' => [
                    'LOWER(CAST(Item.nome AS TEXT)) LIKE' => '%' . strtolower($termoBusca) . '%',
                    'LOWER(CAST(Catmat.codigo_catmat AS TEXT)) LIKE' => '%' . strtolower($termoBusca) . '%',
                    'LOWER(CAST(Item.unidade AS TEXT)) LIKE' => '%' . strtolower($termoBusca) . '%',
                    'LOWER(CAST(Item.tipo_item AS TEXT)) LIKE' => '%' . strtolower($termoBusca) . '%',
                ]
            ])
            ->groupBy(['Item.id', 'Catmat.codigo_catmat', 'Catmat.data', 'Catmat.item_id']);

        return $query->toArray();
    }

    public function softDelete(int $id): array
    {
        $item = $this->itemTable->find()
            ->where(['id' => $id])
            ->first();

        if (!$item) {
            return [
                'status' => 'error',
                'message' => 'Item not found.'
            ];
        }

        $item->is_ativo = false;

        if ($this->itemTable->save($item)) {
            return [
                'status' => 'success',
                'message' => 'The item has been soft deleted.',
                'data' => $item
            ];
        }

        return [
            'status' => 'error',
            'message' => 'The item could not be soft deleted. Please, try again.',
            'errors' => $item->getErrors()
        ];
    }


}
