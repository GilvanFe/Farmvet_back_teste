<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use App\Service\FornecedorService;
use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * Fornecedor Controller
 *
 * @property \App\Model\Table\FornecedorTable $Fornecedor
 */
class FornecedorController extends AppController
{
    /**
     * @var \App\Service\FornecedorService
     */
    protected FornecedorService $fornecedorService;

    public function initialize(): void
    {
        parent::initialize();
        $fornecedorTable = $this->getTableLocator()->get('Fornecedor');
        $this->fornecedorService = new FornecedorService($fornecedorTable);
    }

    /**
     * Handle CORS preflight and always send CORS headers.
     */
    public function beforeFilter(EventInterface $event): ?Response
    {
        $this->response = $this->response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');

        if ($this->request->getMethod() === 'OPTIONS') {
            // Immediately respond to preflight
            return $this->response;
        }

        return parent::beforeFilter($event);
    }

    public function paginateArray($data, $limit, $page)
    {
        $offset = ($page - 1) * $limit;
        return array_slice($data, $offset, $limit);
    }

    public function searchFornecedorNome(): ?Response
    {
        $this->autoRender = false;

        $searchTerm = $this->request->getQuery('query', '');
        $page = (int)$this->request->getQuery('page', 1);
        $limit = (int)$this->request->getQuery('limit', 60);

        $fornecedorArray = $this->fornecedorService->searchFornecedorPorNome($searchTerm);

        $totalFornecedor = count($fornecedorArray);
        $totalPages = (int)ceil($totalFornecedor / $limit);

        $offset = ($page - 1) * $limit;
        $paginatedFornecedor = array_slice($fornecedorArray, $offset, $limit);

        $response = [
            'data' => $paginatedFornecedor,
            'pagination' => [
                'current_page'     => $page,
                'total_pages'      => $totalPages,
                'total_fornecedor' => $totalFornecedor,
                'limit'            => $limit,
            ],
        ];

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($response));
    }

    public function index()
    {
        $query = $this->Fornecedor->find();
        $fornecedor = $this->paginate($query);
        $this->set(compact('fornecedor'));
    }

    public function getAll(): ?Response
    {
        $this->autoRender = false;
        $result = $this->fornecedorService->getAllFornecedores();

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($result))
            ->withStatus($result['success'] ? 200 : 404);
    }

    public function getFornecedorById(int $id): ?Response
    {
        $this->autoRender = false;

        $fornecedor = $this->fornecedorService->getFornecedorById($id);
        if ($fornecedor['success']) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode($fornecedor))
                ->withStatus(200);
        }

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode(['message' => 'Fornecedor não encontrado']))
            ->withStatus(404);
    }

    public function add(): ?Response
    {
        $this->autoRender = false;

        // parse JSON if getData() is empty
        $data = $this->request->getData() ?: json_decode((string)$this->request->input(), true) ?? [];

        if (!$this->request->is('post')) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Método de requisição inválido.'
                ]))
                ->withStatus(405);
        }

        $result = $this->fornecedorService->createFornecedor($data);
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($result))
            ->withStatus($result['success'] ? 201 : 400);
    }

    public function edit(int $id): ?Response
    {
        $this->autoRender = false;

        // parse JSON if getData() is empty
        $data = $this->request->getData() ?: json_decode((string)$this->request->input(), true) ?? [];

        if (!$this->request->is(['patch', 'post', 'put'])) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Método de requisição inválido.'
                ]))
                ->withStatus(405);
        }

        $result = $this->fornecedorService->updateFornecedor($id, $data);
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($result))
            ->withStatus($result['success'] ? 200 : 400);
    }

    public function delete(int $id): ?Response
    {
        $this->autoRender = false;

        $this->request->allowMethod(['post', 'delete']);
        $result = $this->fornecedorService->deleteFornecedor($id);

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($result))
            ->withStatus($result['success'] ? 200 : 404);
    }
}
