<?php
declare(strict_types=1);

namespace App\Controller;
use Cake\Http\Response;
use App\Service\ItemService;


/**
 * @OA\Tag(
 *   name="Item",
 *   description="Operações com os itens do estoque"
 * )
 */
class ItemController extends AppController
{


    private ItemService $itemService;

    public function initialize(): void
    {
        parent::initialize();

        $itemTable = $this->getTableLocator()->get('Item');
        $catmatTable = $this->getTableLocator()->get('Catmat');

        $this->itemService = new ItemService($itemTable, $catmatTable);
    }

    public function paginateArray($data, $limit, $page)
    {
        $offset = ($page - 1) * $limit;
        return array_slice($data, $offset, $limit);
    }

/**
     * Lista itens com paginação.
     *
     * @OA\Get(
     *   path="/item",
     *   tags={"Item"},
     *   summary="Lista itens com paginação",
     *   @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Lista de itens paginada")
     * )
     */
    public function index(): ?Response
    {
        $this->autoRender = false;

        $itemsArray = $this->itemService->getItemsUltimoCatmat();

        $limit = 60;
        $page = $this->request->getQuery('page', 1); 

        $paginatedItems = $this->paginateArray($itemsArray, $limit, $page);

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($paginatedItems));
    }

    /**
     * Busca itens por nome.
     *
     * @OA\Get(
     *   path="/item/search",
     *   tags={"Item"},
     *   summary="Busca itens com base em um termo",
     *   @OA\Parameter(name="query", in="query", required=true, @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="Itens encontrados")
     * )
     */
    public function searchItems(): ?Response
    {
        $this->autoRender = false;
        $searchTerm = $this->request->getQuery('query', '');

        $itemsArray = $this->itemService->searchItems($searchTerm);

        $limit = 60;

        $paginatedItems = array_slice($itemsArray, 0, $limit);

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($paginatedItems));
    }

    /**
     * Busca itens por nome com filtro de ativos.
     *
     * @OA\Get(
     *   path="/item/buscar",
     *   tags={"Item"},
     *   summary="Busca itens ativos por nome com paginação",
     *   @OA\Parameter(name="query", in="query", required=false, @OA\Schema(type="string")),
     *   @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Itens encontrados")
     * )
     */
    public function buscarItem(): ?Response
    {
        $this->autoRender = false;
        
        $searchTerm = $this->request->getQuery('query', ''); 
        $page = (int) $this->request->getQuery('page', 1); 
        $limit = (int) $this->request->getQuery('limit', 60); 

        $itemArray = array_filter(
            $this->itemService->buscarPorNome($searchTerm),
            fn($item) => $item['is_ativo'] === true
        );

        $totalItem = count($itemArray);
        $totalPages = (int) ceil($totalItem / $limit);

        $offset = ($page - 1) * $limit;
        $paginatedItem = array_slice($itemArray, $offset, $limit);

        $response = [
            'data' => $paginatedItem ?? [],
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_itens' => $totalItem,
                'limit' => $limit,
            ],
        ];

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($response));
    }




        /**
     * Retorna os detalhes de um item específico.
     *
     * @OA\Get(
     *   path="/item/{id}",
     *   tags={"Item"},
     *   summary="Obtém os dados de um item pelo ID",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID do item",
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Dados do item retornados com sucesso"
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Item não encontrado"
     *   )
     * )
     */

    public function view($id)
    {
        $this->autoRender = false;

        $result = $this->itemService->getItemById((int)$id);
        $this->response = $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'status' => $result['status'],
                'data' => $result['status'] === 'success' ? $result['data'] : null,
                'message' => $result['status'] === 'error' ? $result['message'] : null
            ]));
    }

    /**
     * Cria um novo item.
     *
     * @OA\Post(
     *   path="/item/adicionar",
     *   tags={"Item"},
     *   summary="Cria um novo item de estoque",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"nome", "tipo_item", "unidade", "estoque_minimo"},
     *       @OA\Property(property="nome", type="string", example="Dipirona"),
     *       @OA\Property(property="tipo_item", type="string", example="medicamento"),
     *       @OA\Property(property="unidade", type="string", example="caixa"),
     *       @OA\Property(property="estoque_minimo", type="integer", example=10),
     *       @OA\Property(property="is_controlado", type="boolean", example=false),
     *       @OA\Property(property="descricao_completa", type="string", example="Analgésico e antipirético"),
     *       @OA\Property(property="descricao_complementar", type="string", example="Uso oral"),
     *       @OA\Property(property="legislacao_especifica", type="string", example="Portaria MS 344"),
     *       @OA\Property(property="observacao", type="string", example="Armazenar em temperatura ambiente"),
     *       @OA\Property(property="codigo_catmat", type="string", example="123456")
     *     )
     *   ),
     *   @OA\Response(response=201, description="Item criado com sucesso"),
     *   @OA\Response(response=400, description="Erro na criação do item")
     * )
     */
    public function add(): ?Response
    {
        $this->autoRender = false;
        $data = $this->request->getData();
    
        if ($this->request->is('post')) {
            $result = $this->itemService->createItemWithCatmat($data);
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode($result));
        }
        $result = [
            'status' => 'error',
            'message' => 'Invalid request method.'
        ];
    
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($result));
    }
    
    /**
     * Inativa logicamente um item (soft delete).
     *
     * @OA\Delete(
     *   path="/item/deletar/{id}",
     *   tags={"Item"},
     *   summary="Inativa logicamente um item",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="Item inativado com sucesso"),
     *   @OA\Response(response=404, description="Item não encontrado ou erro ao inativar")
     * )
     */
    public function softDelete($id)
    {
        $this->autoRender = false;

        $this->request->allowMethod(['patch', 'post', 'put']);

        $result = $this->itemService->softDelete((int)$id);

        return $this->response->withType('application/json')
                            ->withStringBody(json_encode($result));
    }




/**
     * Atualiza um item existente.
     *
     * @OA\Put(
     *   path="/item/{id}",
     *   tags={"Item"},
     *   summary="Atualiza dados de um item",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       @OA\Property(property="nome", type="string", example="Dipirona Atualizado"),
     *       @OA\Property(property="tipo_item", type="string", example="medicamento"),
     *       @OA\Property(property="unidade", type="string", example="caixa"),
     *       @OA\Property(property="estoque_minimo", type="integer", example=5),
     *       @OA\Property(property="descricao_completa", type="string", example="Analgésico atualizado"),
     *       @OA\Property(property="descricao_complementar", type="string", example="Novo uso"),
     *       @OA\Property(property="legislacao_especifica", type="string", example="Portaria MS 345"),
     *       @OA\Property(property="observacao", type="string", example="Atualizado"),
     *       @OA\Property(property="codigo_catmat", type="string", example="654321")
     *     )
     *   ),
     *   @OA\Response(response=200, description="Item atualizado com sucesso"),
     *   @OA\Response(response=400, description="Erro na atualização do item")
     * )
     */
    public function edit($id = null)
    {
        $this->autoRender = false;
        
        $this->request->allowMethod(['patch', 'post', 'put']);
        
        $item = $this->Item->get($id, ['contain' => []]);
        if (!$item) {
            return $this->response->withType('application/json')
                                ->withStringBody(json_encode([
                                    'status' => 'error',
                                    'message' => 'Item not found.'
                                ]));
        }

        
        $data = $this->request->getData();
        $result = $this->itemService->updateItemAndMaybeAddCatmat($id, $data);
        if ($result['status'] === 'success') {
            return $this->response->withType('application/json')
                                ->withStringBody(json_encode([
                                    'status' => 'success',
                                    'data' => $result['data'],
                                    'message' => 'Item updated successfully.'
                                ]));
        } else {
            return $this->response->withType('application/json')
                                ->withStringBody(json_encode([
                                    'status' => 'error',
                                    'message' => $result['message'], $result['data']
                                ]));
        }

        $item = $this->Item->patchEntity($item, $this->request->getData());
        if ($this->Item->save($item)) {
            return $this->response->withType('application/json')
                                ->withStringBody(json_encode([
                                    'status' => 'success',
                                    'message' => 'The item has been saved.'
                                ]));
        }

    }
}
