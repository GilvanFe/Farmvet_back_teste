<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;
use App\Service\MovimentacaoService;
use App\Service\LoteService;
use Cake\ORM\TableRegistry;
use Cake\Log\Log;

/**
 * Movimentacao Controller
 *
 * @property \App\Model\Table\MovimentacaoTable $Movimentacao
 */
class MovimentacaoController extends AppController
{
    private MovimentacaoService $movimentacaoService;

    public function initialize(): void
    {
        parent::initialize();

        $movimentacaoTable = $this->getTableLocator()->get('Movimentacao');
        $loteService = new LoteService(TableRegistry::getTableLocator()->get('Lote'));
        $this->movimentacaoService = new MovimentacaoService($movimentacaoTable, $loteService);
    }

    /**
     * @OA\Get(
     *   path="/movimentacao/listar",
     *   tags={"Movimentacoes"},
     *   summary="Lista todas as movimentações com paginação",
     *   @OA\Parameter(
     *     name="page", in="query", required=false, @OA\Schema(type="integer"), description="Página atual"
     *   ),
     *   @OA\Parameter(
     *     name="limit", in="query", required=false, @OA\Schema(type="integer"), description="Quantidade por página"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Lista paginada de movimentações"
     *   )
     * )
     */
    public function index(): ?Response
    {
        $this->autoRender = false;
        $movimentacoesArray = $this->movimentacaoService->getAllMovimentacoes();

        $limit = (int) $this->request->getQuery('limit', 60);
        $page = (int) $this->request->getQuery('page', 1);
        $offset = ($page - 1) * $limit;

        $paginated = array_slice($movimentacoesArray, $offset, $limit);

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode([
                'data' => $paginated,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil(count($movimentacoesArray) / $limit),
                    'total' => count($movimentacoesArray),
                    'limit' => $limit
                ]
            ]));
    }

    public function searchMovimentacaoSaida(): ?Response
    {
        $this->autoRender = false;
        
        $searchTerm = $this->request->getQuery('query', ''); 
        $page = (int) $this->request->getQuery('page', 1); 
        $limit = (int) $this->request->getQuery('limit', 60); 

        $movimentacoesArray = $this->movimentacaoService->searchMovimentacaoSaida($searchTerm);

        $totalMovimentacao = count($movimentacoesArray);
        $totalPages = (int) ceil($totalMovimentacao / $limit);

        $offset = ($page - 1) * $limit;
        $paginatedMovimentacoes = array_slice($movimentacoesArray, $offset, $limit);

        $response = [
            'data' => $paginatedMovimentacoes,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_movimentacao' => $totalMovimentacao,
                'limit' => $limit,
            ],
        ];

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($response));
    }

    public function searchMovimentacaoEntrada(): ?Response
    {
        $this->autoRender = false;

        $searchTerm = $this->request->getQuery('query', '');
        $page = (int) $this->request->getQuery('page', 1);
        $limit = (int) $this->request->getQuery('limit', 60);

        try {
            $loteArray = $this->movimentacaoService->searchMovimentacaoEntrada($searchTerm);

            Log::write('debug', 'Resultado da busca entrada: ' . json_encode($loteArray));

            $totalLotes = count($loteArray);
            $totalPages = (int) ceil($totalLotes / $limit);
            $offset = ($page - 1) * $limit;
            $paginatedLotes = array_slice($loteArray, $offset, $limit);

            $response = [
                'data' => $paginatedLotes,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_lotes' => $totalLotes,
                    'limit' => $limit,
                ],
            ];

            return $this->response->withType('application/json')->withStringBody(json_encode($response));
        } catch (\Exception $e) {
            Log::write('error', 'Erro em searchMovimentacaoEntrada: ' . $e->getMessage());
            return $this->response->withStatus(500)->withType('application/json')->withStringBody(json_encode([
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ]));
        }
    }



    public function searchMovimentacaoVencimento(): ?Response
    {
        $this->autoRender = false;
        $searchTerm = $this->request->getQuery('query', ''); 
        $page = (int) $this->request->getQuery('page', 1); 
        $limit = (int) $this->request->getQuery('limit', 60); 

        $movimentacoesArray = $this->movimentacaoService->searchMovimentacaoVencimento($searchTerm);

        $totalMovimentacao = count($movimentacoesArray);
        $totalPages = (int) ceil($totalMovimentacao / $limit);

        $offset = ($page - 1) * $limit;
        $paginatedMovimentacoes = array_slice($movimentacoesArray, $offset, $limit);

        $response = [
            'data' => $paginatedMovimentacoes,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_movimentacao' => $totalMovimentacao,
                'limit' => $limit,
            ],
        ];

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($response));
    }
        
    public function searchMovimentacaoPerda(): ?Response
    {
        $this->autoRender = false;
        
        $searchTerm = $this->request->getQuery('query', ''); 
        $page = (int) $this->request->getQuery('page', 1); 
        $limit = (int) $this->request->getQuery('limit', 60); 

        $movimentacoesArray = $this->movimentacaoService->searchMovimentacaoPerda($searchTerm);

        $totalMovimentacao = count($movimentacoesArray);
        $totalPages = (int) ceil($totalMovimentacao / $limit);

        $offset = ($page - 1) * $limit;
        $paginatedMovimentacoes = array_slice($movimentacoesArray, $offset, $limit);

        $response = [
            'data' => $paginatedMovimentacoes,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_movimentacao' => $totalMovimentacao,
                'limit' => $limit,
            ],
        ];

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($response));
    }

    public function view($id = null)
    {
        $movimentacao = $this->movimentacaoService->getMovimentacaoById($id);
        if (!$movimentacao) {
            $this->Flash->error(__('Movimentação não encontrada.'));
            return $this->redirect(['action' => 'index']);
        }
        $this->set(compact('movimentacao'));
    }

    /**
     * Cria uma nova movimentação (entrada ou saída)
     *
     * @OA\Post(
     *   path="/movimentacoes",
     *   tags={"Movimentacoes"},
     *   summary="Registra uma movimentação de entrada ou saída",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/MovimentacaoEntrada")
     *       required={"tipo_movimentacao","subtipo_movimentacao","data","lotes"},
     *       @OA\Property(property="tipo_movimentacao", type="string", example="entrada"),
     *       @OA\Property(property="subtipo_movimentacao", type="string", example="compra"),
     *       @OA\Property(property="data", type="string", format="date", example="2025-05-01"),
     *       @OA\Property(property="fornecedor", type="string", example="LabTest"),
     *       @OA\Property(property="via_compra", type="string", example="Pregão"),
     *       @OA\Property(property="documento_origem", type="string", example="NF123456"),
     *       @OA\Property(
     *         property="lotes",
     *         type="array",
     *         @OA\Items(
     *           @OA\Property(property="item_id", type="integer", example=1),
     *           @OA\Property(property="numero_lote", type="string", example="L001"),
     *           @OA\Property(property="data_vencimento", type="string", format="date", example="2026-01-01"),
     *           @OA\Property(property="quantidade", type="integer", example=10),
     *           @OA\Property(property="valor_unitario", type="number", format="float", example=2.5)
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Movimentação criada com sucesso"
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Erro na criação da movimentação"
     *   )
     * )
     *
     * @return Response|null
     */
    public function add(): ?Response
    {
        $this->autoRender = false;

        Log::write('info', 'Payload recebido para perda: ' . json_encode($this->request->getData()));

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $result = $this->movimentacaoService->registrarMovimentacao($data);

            if ($result['success']) {
                return $this->response->withStatus(201)->withType('application/json')->withStringBody(json_encode([
                    'status' => 'success',
                    'message' => $result['message']
                ]));
            } else {
                return $this->response->withStatus(400)->withType('application/json')->withStringBody(json_encode($result));
            }
        }

        $response = [
            'status' => 'error',
            'message' => __('Método não permitido. Use POST.'),
        ];

        return $this->response->withType('application/json')->withStringBody(json_encode($response))->withStatus(405); // Código HTTP 405 (Method Not Allowed)
    }
    /**
     * Lista movimentações de um lote específico
     *
     * @OA\Get(
     *   path="/movimentacao/lote/{id}",
     *   tags={"Movimentacao"},
     *   summary="Lista lotes por ID da movimentacaoId",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer"),
     *     description="ID do lote"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Lista de movimentações do lote"
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Lote não encontrado"
     *   )
     * )
     */
    public function listarPorLoteId($id = null): ?Response
    {
        $this->autoRender = false;

        if ($id === null) {
            return $this->response->withStatus(400)->withType('application/json')->withStringBody(json_encode([
                'status' => 'error',
                'message' => 'ID do lote não informado.'
            ]));
        }

        try {
            $movimentacaoId = (int)$id;
            $movimentacoes = $this->movimentacaoService->getMovimentacoesByLoteId($movimentacaoId);

            if (empty($movimentacoes)) {
                return $this->response->withStatus(404)->withType('application/json')->withStringBody(json_encode([
                    'status' => 'error',
                    'message' => 'Nenhuma movimentação encontrada para este lote.'
                ]));
            }

            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'data' => $movimentacoes
                ]));
        } catch (\Exception $e) {
            Log::write('error', 'Erro em listarPorLoteId: ' . $e->getMessage());
            return $this->response->withStatus(500)->withType('application/json')->withStringBody(json_encode([
                'status' => 'error',
                'message' => 'Erro interno: ' . $e->getMessage()
            ]));
        }
    }
}