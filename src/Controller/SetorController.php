<?php
declare(strict_types=1);

namespace App\Controller;
use Cake\Http\Response;
use App\Service\SetorService;


/**
 * Setor Controller
 *
 * @property \App\Model\Table\SetorTable $Setor
 */
class SetorController extends AppController
{


    protected SetorService $setorService;

    public function initialize(): void
    {
        parent::initialize();
        $setorTable = $this->getTableLocator()->get('Setor');
        $this->setorService = new SetorService($setorTable);

    }


    public function index()
    {
        $this->autoRender = false;
    }

    public function getAll(): ?Response
    {
        $this->autoRender = false; 
        $result = $this->setorService->getAllSetores(); 

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($result))
            ->withStatus($result['success'] ? 200 : 404);
    }
    /**
     * Search method
     *
     * @return \Cake\Http\Response|null JSON response with search results
     */
    public function search(): ?Response
    {
        $this->autoRender = false;

        $searchTerm = $this->request->getQuery('query', '');
        $page = (int) $this->request->getQuery('page', 1);
        $limit = (int) $this->request->getQuery('limit', 60);

        $setoresArray = $this->setorService->searchSetores($searchTerm);

        $totalSetores = count($setoresArray);
        $totalPages = (int) ceil($totalSetores / $limit);

        $offset = ($page - 1) * $limit;
        $paginatedSetores = array_slice($setoresArray, $offset, $limit);

        $response = [
            'data' => $paginatedSetores,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_setores' => $totalSetores,
                'limit' => $limit,
            ],
        ];

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($response));
    }

    /**
     * View method
     *
     * @param string|null $id Setor id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $setor = $this->Setor->get($id, contain: []);
        $this->set(compact('setor'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
{
    $this->autoRender = false;
    $data = $this->request->getData();
    
    if ($this->request->is('post')) {
        $result = $this->setorService->createSetor($data);
        if ($result['success']) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode($result))
                ->withStatus(201);
        } else {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode($result))
                ->withStatus(400);
        }
    }

    $result = [
        'status' => 'error',
        'message' => 'metodo de requisicao invalido'
    ];

    return $this->response
        ->withType('application/json')
        ->withStringBody(json_encode($result))
        ->withStatus(405);
}

    /**
     * Edit method
     *
     * @param string|null $id Setor id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null): ?Response
    {
        $this->autoRender = false;
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $result = $this->setorService->updateSetor($id, $data);

            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode($result))
                ->withStatus($result['success'] ? 200 : 400);
        }

        $result = [
            'status' => 'error',
            'message' => 'metodo de requisicao invalido'
        ];

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($result))
            ->withStatus(405);
    }

    /**
     * Soft Delete method
     *
     * @param string|null $id Setor id.
     * @return \Cake\Http\Response|null JSON response
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null): ?Response
    {
        $this->autoRender = false;
        $this->request->allowMethod(['post', 'delete']);

        $result = $this->setorService->softDeleteSetor($id);

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($result))
            ->withStatus($result['success'] ? 200 : 400);
    }
}
