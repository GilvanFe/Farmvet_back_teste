<?php

namespace App\Service;

use App\Model\Table\SetorTable;
use Cake\ORM\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\TableRegistry;

class SetorService
{


    private SetorTable $setorTable;

    public function __construct()
    {

        $this->setorTable = TableRegistry::getTableLocator()->get('Setor');;
    }

    /**
     * @param array $data dados do setor
     * @return array Resultado da operação
     *
     */

    public function createSetor(array $data): array
    {
        $setor = $this->setorTable->newEntity($data);

        if ($this->setorTable->save($setor)) {
            return [
                'success' => true,
                'message' => 'O setor foi salvo com sucesso.',
                'data' => $setor
            ];
        }

        return [
            'success' => false,
            'message' => 'O setor não pode ser salvo, tente novamente.',
            'errors' => $setor->getErrors()
        ];
    }


    /**
     * Busca setores filtrando por nome e código do fornecedor.
     *
     * @param array $filters Filtros de busca (nome, codigo)
     * @return array Resultado da busca
     */
    public function searchSetores($filters): array
    {
        $query = $this->setorTable->find();

        $query->where(['Setor.is_ativo' => true]);

        if (isset($filters) && !empty($filters)) {
            $query->where([
                'OR' => [
                    ['Setor.nome ILIKE' => '%' . $filters . '%'],
                    ['Setor.codigo ILIKE' => '%' . $filters . '%']
                ]
            ]);
        }

        $setores = $query->toArray();

        if (!empty($setores)) {
            return [
                'success' => true,
                'data' => $setores
            ];
        }

        return [
            'success' => false,
            'message' => 'Nenhum setor encontrado com os filtros fornecidos.'
        ];
    }

    public function getAllSetores(): array
    {
        $setores = $this->setorTable->find('all')->toArray();

        if (!empty($setores)) {
            return [
                'success' => true,
                'data' => $setores
            ];
        }

        return [
            'success' => false,
            'message' => 'Nenhum setor encontrado.'
        ];
    }

    /**
     * Soft delete a setor by setting its 'deleted' flag.
     *
     * @param string|int $id Setor id.
     * @return array Resultado da operação
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function softDeleteSetor($id): array
    {
        $setor = $this->setorTable->get($id);

        $setor->is_ativo = false;

        if ($this->setorTable->save($setor)) {
            return [
                'success' => true,
                'message' => 'Setor excluído com sucesso (soft delete).'
            ];
        }

        return [
            'success' => false,
            'message' => 'Não foi possível excluir o setor.'
        ];
    }
    
    /**
     * Atualiza um setor existente.
     *
     * @param int|string $id ID do setor a ser atualizado
     * @param array $data Dados para atualização
     * @return array Resultado da operação
     */
    public function updateSetor($id, array $data): array
    {
        try {
            $setor = $this->setorTable->get($id);
            $setor = $this->setorTable->patchEntity($setor, $data);

            if ($this->setorTable->save($setor)) {
                return [
                    'success' => true,
                    'message' => 'Setor atualizado com sucesso.',
                    'data' => $setor
                ];
            }

            return [
                'success' => false,
                'message' => 'Não foi possível atualizar o setor.',
                'errors' => $setor->getErrors()
            ];
        } catch (RecordNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Setor não encontrado.'
            ];
        }
    }

}
