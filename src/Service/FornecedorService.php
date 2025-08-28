<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Table\FornecedorTable;
use Cake\ORM\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\TableRegistry;

class FornecedorService
{
    private FornecedorTable $fornecedorTable;

    public function __construct()
    {
        $this->fornecedorTable = TableRegistry::getTableLocator()->get('Fornecedor');
    }
    public function createFornecedor(array $data): array
    {
        $fornecedor = $this->fornecedorTable->newEmptyEntity();
        $fornecedor = $this->fornecedorTable->patchEntity($fornecedor, $data);

        if ($this->fornecedorTable->save($fornecedor)) {
            return [
                'success' => true,
                'message' => 'O fornecedor foi salvo com sucesso.',
                'data' => $fornecedor
            ];
        }
        return [
            'success' => false,
            'message' => 'O fornecedor nao pode ser salvo, tente novamente',
            'errors' => $fornecedor->getErrors()
        ];
    }

    public function getAllFornecedores(): array
    {
        $fornecedor = $this->fornecedorTable->find('all')->toArray();

        if (!empty($fornecedor)) {
            return [
                'success' => true,
                'data' => $fornecedor
            ];
        }

        return [
            'success' => false,
            'message' => 'Nenhum fornecedor encontrado.'
        ];
    }

    public function getFornecedorById(int $id): ?array
    {
        try {
            $fornecedor = $this->fornecedorTable->get($id);
            return [
                'success' => true,
                'data' => $fornecedor
            ];
        } catch (RecordNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Fornecedor não encontrado.'
            ];
        }
    }


     public function searchFornecedorPorNome(string $searchTerm): array
    {
        $query = $this->fornecedorTable->find('all');

        if (!empty($searchTerm)) {
            $query->where(['LOWER(nome) LIKE' => '%' . strtolower($searchTerm) . '%']);
        }

        return $query->toArray();
    }

        /**
     * Atualiza um fornecedor existente.
     *
     * @param int   $id
     * @param array $data
     * @return array
     */
    public function updateFornecedor(int $id, array $data): array
    {
        try {
            $fornecedor = $this->fornecedorTable->get($id);
            $fornecedor = $this->fornecedorTable->patchEntity($fornecedor, $data);

            if ($this->fornecedorTable->save($fornecedor)) {
                return [
                    'success' => true,
                    'message' => 'Fornecedor atualizado com sucesso.',
                    'data'    => $fornecedor
                ];
            }

            return [
                'success' => false,
                'message' => 'Não foi possível atualizar o fornecedor.',
                'errors'  => $fornecedor->getErrors()
            ];
        } catch (RecordNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Fornecedor não encontrado.'
            ];
        }
    }

    /**
     * Exclui um fornecedor pelo ID.
     *
     * @param int $id
     * @return array
     */
    public function deleteFornecedor(int $id): array
    {
        try {
            $fornecedor = $this->fornecedorTable->get($id);

            if ($this->fornecedorTable->delete($fornecedor)) {
                return [
                    'success' => true,
                    'message' => 'Fornecedor excluído com sucesso.'
                ];
            }

            return [
                'success' => false,
                'message' => 'Falha ao excluir o fornecedor.'
            ];
        } catch (RecordNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Fornecedor não encontrado.'
            ];
        }
    }
}

