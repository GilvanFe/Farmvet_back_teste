<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Exception\BadRequestException;
use Cake\Http\Response;
use App\Service\LoteService;
use App\Service\ItemService;
use App\Service\FornecedorService;
use Exception;

/**
 * @OA\Tag(
 *   name="Lotes",
 *   description="Operações de gerenciamento de lotes"
 * )
 */
class LoteController extends AppController
{
    protected LoteService $loteService;
    protected FornecedorService $fornecedorService;

    /**
     * @throws Exception
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loteService = new LoteService();
        $this->fornecedorService = new FornecedorService();
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->autoRender = false;
        $query = $this->Lote->find();
        $lotes = $this->paginate($query);
        $this->response = $this->response->withType('application/json')
                                     ->withStringBody(json_encode($lotes->toArray()));
    }

    /**
     * Cria um novo lote.
     *
     * @OA\Post(
     *   path="/lotes",
     *   tags={"Lotes"},
     *   summary="Cria um novo lote",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"item_id", "numero_lote", "data_vencimento", "quantidade_atual"},
     *       @OA\Property(property="item_id", type="integer", example=1),
     *       @OA\Property(property="numero_lote", type="string", example="L123"),
     *       @OA\Property(property="data_vencimento", type="string", format="date", example="2025-12-31"),
     *       @OA\Property(property="quantidade_atual", type="integer", example=20),
     *       @OA\Property(property="valor_unitario", type="number", format="float", example=5.5)
     *     )
     *   ),
     *   @OA\Response(response=201, description="Lote criado com sucesso"),
     *   @OA\Response(response=400, description="Erro ao criar lote")
     * )
     */
    public function add(): ?Response
    {
        $this->autoRender = false;
        $data = $this->request->getData();

        if ($this->request->is('post')) {
            $result = $this->loteService->createLote($data);

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
            'message' => 'Método de requisição inválido'
        ];

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($result))
            ->withStatus(405);
    }

    /**
     * Lista os lotes de um item específico.
     *
     * @OA\Get(
     *   path="/lotes/item/{itemId}",
     *   tags={"Lotes"},
     *   summary="Lista lotes por item",
     *   @OA\Parameter(name="itemId", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Lista de lotes do item")
     * )
     */
    public function porItem(int $itemId)
    {
        $this->request->allowMethod(['get']);
        $this->autoRender = false;
        $lotes = $this->loteService->getLotesByItemId($itemId)->toArray();
        if (empty($lotes)) {
            throw new NotFoundException(__('Nenhum lote encontrado para este item.'));
        }
        $this->response = $this->response->withType('application/json')
                                        ->withStringBody(json_encode([
                                            'success' => true,
                                            'data' => $lotes,
                                        ]));

    }


    /**
     * Lista lotes próximos do vencimento.
     *
     * @OA\Get(
     *   path="/lotes/proximos-vencimento",
     *   tags={"Lotes"},
     *   summary="Lista lotes próximos do vencimento",
     *   @OA\Parameter(
     *     name="dias",
     *     in="query",
     *     required=false,
     *     description="Quantidade de dias para considerar como próximo do vencimento (padrão: 30)",
     *     @OA\Schema(type="integer", default=30)
     *   ),
     *   @OA\Response(response=200, description="Lista de lotes próximos do vencimento")
     * )
     */
    public function lotesProximosVencimento()
    {
        $this->autoRender = false;
        $dias = (int)($this->request->getQuery('dias') ?? 30);

        try {
            $lotes = $this->loteService->lotesProximosVencimento($dias);

            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'data' => $lotes
                ]));
        } catch (\Exception $e) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]))
                ->withStatus(500);
        }
    }

    /**
     * Edita um lote existente.
     *
     * @param int|null $id ID do lote.
     * @return \Cake\Http\Response|null
     */
    public function edit($id = null)
    {
        $this->autoRender = false;

        if (!$this->request->is(['patch', 'post', 'put'])) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'status' => 'error',
                    'message' => 'Método de requisição inválido'
                ]))
                ->withStatus(405);
        }

        $data = $this->request->getData();
        $result = $this->loteService->editLote((int)$id, $data);

        if ($result['success']) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode($result))
                ->withStatus(200);
        } else {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode($result))
                ->withStatus(400);
        }
    }

    public function buscar($id)
    {

        $this->autoRender = false;

        $result = $this->loteService->buscarLoteById((int)$id);

        // Define o tipo de conteúdo como JSON
        $this->response = $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'status' => $result['status'],
                'data' => $result['status'] === 'success' ? $result['data'] : null,
                'message' => $result['status'] === 'error' ? $result['message'] : null
            ]));
    }

    public function calcularDebito(int $itemId, int $quantidade)
    {
        $this->request->allowMethod(['get']);
        $this->autoRender = false;
        $resultado = $this->loteService->calcularDebito($itemId, $quantidade);
        $this->response = $this->response->withType('application/json')
                                     ->withStringBody(json_encode($resultado));
    }

    public function verificarEstoqueMinimo()
    {
        $resultado = $this->loteService->verificarEstoqueMinimoParaTodosItens();

        $this->autoRender = false;
        $this->response = $this->response->withType('application/json')
                                         ->withStringBody(json_encode($resultado));
    }
    public function getValorTotalDeTodosOsLotes()
    {
        $valorTotal = $this->loteService->getValorTotalDeTodosOsLotes();
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json')
                                         ->withStringBody(json_encode(['valor_total' => $valorTotal]));
    }
}
