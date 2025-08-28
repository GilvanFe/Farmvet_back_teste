<?php

namespace App\Controller;

use App\Service\FornecedorService;
use App\Service\RelatorioService;
use Cake\Http\Exception\BadRequestException;
use Cake\Validation\Validator;
use DateMalformedStringException;
use DateTime;
use Exception;

class RelatorioController extends AppController
{
    protected RelatorioService $relatorioService;
    protected FornecedorService $fornecedorService;

    /**
     * @throws Exception
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->relatorioService = new RelatorioService();
        $this->fornecedorService = new FornecedorService();
    }

    /**
     * Rota raiz /relatorio
     *
     * @throws BadRequestException
     */
    public function index(): void
    {
        // Proxy para o método de dispatch baseado em ?tipo=
        $this->getRelatorioMovimentacao();
    }

    /**
     * @OA\Get(
     *     path="/relatorio/estoque/posicao",
     *     tags={"Estoque"},
     *     summary="Consulta a posição atual do estoque",
     *     description="Retorna uma visão consolidada da quantidade em estoque por item, agrupando por valor unitário, com possibilidade de filtragem por tipo de item e unidade.",
     *     @OA\Parameter(
     *         name="tipo_item",
     *         in="query",
     *         required=false,
     *         description="Filtra por tipo do item: 'material', 'farmacologico' ou 'medicamento_vet'",
     *         @OA\Schema(type="string", enum={"material", "farmacologico", "medicamento_vet"})
     *     ),
     *     @OA\Parameter(
     *         name="unidade",
     *         in="query",
     *         required=false,
     *         description="Filtra por unidade do item (ex: 'ml', 'mg', 'comprimido')",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consulta realizada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="itens",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="item_id", type="integer", example=1),
     *                         @OA\Property(property="item_nome", type="string", example="Dipirona"),
     *                         @OA\Property(property="quantidade", type="number", format="float", example=100.0),
     *                         @OA\Property(property="unidade_medida", type="string", example="ml"),
     *                         @OA\Property(property="valor_unitario", type="number", format="float", example=2.5),
     *                         @OA\Property(property="valor_total", type="number", format="float", example=250.0)
     *                     )
     *                 ),
     *                 @OA\Property(property="valor_total_estoque", type="number", format="float", example=1200.50),
     *                 @OA\Property(
     *                     property="soma_por_tipo",
     *                     type="object",
     *                     @OA\Property(property="material", type="integer", example=5),
     *                     @OA\Property(property="farmacologico", type="integer", example=3),
     *                     @OA\Property(property="medicamento_vet", type="integer", example=8)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Parâmetro inválido",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Parâmetro 'item_id' deve ser um número inteiro.")
     *         )
     *     )
     * )
     */
    public function posicaoAtual(): void
    {
        $this->autoRender = false;
        $this->request->allowMethod(['get']);

        $data = $this->request->getQuery();

        $validator = new Validator();

        $validator
            ->allowEmptyString('tipo_item')
            ->add('tipo_item', 'inList', [
                'rule' => ['inList', ['material', 'farmacologico', 'medicamento_vet']],
                'message' => "O parâmetro 'tipo_item' deve ser 'material', 'farmacologico' ou 'medicamento_vet'."
            ])
            ->allowEmptyString('tipo_item');;

        $validator
            ->allowEmptyString('unidade')
            ->add('unidade', 'isString', [
                'rule' => function ($value) {
                    return is_string($value);
                },
                'message' => "O parâmetro 'unidade' deve ser uma string."
            ])
            ->allowEmptyString('unidade');;

        $errors = $validator->validate($data);

        if (!empty($errors)) {
            $mensagens = array_map(fn($e) => implode(', ', $e), $errors);
            throw new BadRequestException(implode(' ', $mensagens));
        }

        // Validação passou, extrai os parâmetros
        $tipo_item = $data['tipo_item'] ?? null;
        $unidade = $data['unidade'] ?? null;

        $dados = $this->relatorioService->getPosicaoEstoqueAtual($tipo_item, $unidade);

        if (empty($dados['itens'])) {
            $this->response = $this->response->withStatus(204)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'message' => 'Nenhum dado encontrado para os filtros informados.',
                    'data' => []
                ]));
            return;
        }

        $this->response = $this->response
            ->withStatus(200)
            ->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'data' => $dados,
            ]));
    }

    /**
     * @OA\Get(
     *     path="/relatorio/movimentacao",
     *     tags={"Relatórios"},
     *     summary="Relatório de movimentações (entrada ou saída)",
     *     description="Gera um relatório de movimentações de entrada ou saída, redirecionando para a função específica com base no tipo.",
     *     @OA\Parameter(
     *         name="tipo",
     *         in="query",
     *         required=true,
     *         description="Tipo da movimentação (entrada ou saida)",
     *         @OA\Schema(type="string", enum={"entrada", "saida"})
     *     ),
     *     @OA\Parameter(
     *         name="data_inicio",
     *         in="query",
     *         required=true,
     *         description="Data inicial do período",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="data_fim",
     *         in="query",
     *         required=true,
     *         description="Data final do período",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="subtipo",
     *         in="query",
     *         required=false,
     *         description="Subtipo da movimentação de entrada (ex: compra, devolução)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="tipo_saida",
     *         in="query",
     *         required=false,
     *         description="Tipo da movimentação de saída (ex: consumo famez, empréstimo)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="setor_id",
     *         in="query",
     *         required=false,
     *         description="ID do setor (apenas para saída)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="nome_item",
     *         in="query",
     *         required=false,
     *         description="Nome do item (busca parcial)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="ficha_clinica",
     *         in="query",
     *         required=false,
     *         description="Número da ficha clínica (apenas para saída)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Relatório gerado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro de validação ou tipo inválido"
     *     )
     * )
     */
    public function getRelatorioMovimentacao(): void
    {
        $this->autoRender = false;
        $this->request->allowMethod(['get']);
        $query = $this->request->getQuery();
        $tipo = $query['tipo'] ?? null;

        if ($tipo === 'entrada') {
            $this->relatorioEntradas(); // validação interna
        } elseif ($tipo === 'saida') {
            $this->relatorioSaidas(); // validação interna
        } else {
            throw new BadRequestException("Parâmetro 'tipo' deve ser 'entrada' ou 'saida'.");
        }
    }

    /**
     * @OA\Get(
     *     path="/relatorio/movimentacao",
     *     tags={"Relatórios"},
     *     summary="Relatório de movimentações de saída",
     *     description="Gera um relatório detalhado das movimentações de saída, com filtros por tipo, setor, nome do item, categoriado item e ficha clínica.",
     *     @OA\Parameter(
     *         name="tipo",
     *         in="query",
     *         required=true,
     *         description="Deve ser 'saida' para acionar este relatório",
     *         @OA\Schema(type="string", enum={"saida"})
     *     ),
     *     @OA\Parameter(
     *         name="data_inicio",
     *         in="query",
     *         required=true,
     *         description="Data inicial do período",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="data_fim",
     *         in="query",
     *         required=true,
     *         description="Data final do período",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="tipo_saida",
     *         in="query",
     *         required=false,
     *         description="Tipo da saída (ex: consumo famez, empréstimo)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="setor_id",
     *         in="query",
     *         required=false,
     *         description="ID do setor destino",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="nome_item",
     *         in="query",
     *         required=false,
     *         description="Nome do item (busca parcial)",
     *         @OA\Schema(type="string")
     *     ),
     *      @OA\Parameter(
     *          name="categoria",
     *          in="query",
     *          required=false,
     *          description="Categoria do item",
     *          @OA\Schema(type="string", enum={"material", "farmacologico", "medicamento_vet"})
     *      ),
     *     @OA\Parameter(
     *         name="ficha_clinica",
     *         in="query",
     *         required=false,
     *         description="Número da ficha clínica do animal",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Relatório gerado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro de validação"
     *     )
     * )
     */
    function relatorioSaidas(): void
    {
        $this->autoRender = false;
        $this->request->allowMethod(['get']);
        $data = $this->request->getQuery();

        $validator = new Validator();
        $validator
            ->requirePresence('data_inicio', 'create', 'A data de início é obrigatória.')
            ->notEmptyDate('data_inicio', 'A data de início não pode estar vazia.')
            ->add('data_inicio', 'formatoData', [
                'rule' => function ($value) {
                    return DateTime::createFromFormat('d-m-Y', $value) !== false;
                },
                'message' => 'Formato inválido. Use Dia-Mes-Ano (ex: 25-06-2024).'
            ])

            ->requirePresence('data_fim', 'create', 'A data de fim é obrigatória.')
            ->notEmptyDate('data_fim', 'A data de fim não pode estar vazia.')
            ->add('data_fim', 'formatoData', [
                'rule' => function ($value) {
                    return DateTime::createFromFormat('d-m-Y', $value) !== false;
                },
                'message' => 'Formato inválido. Use Dia-Mes-Ano (ex: 25-06-2024).'
            ])

            ->allowEmptyString('tipo_saida')
            ->allowEmptyString('setor_id')
            ->allowEmptyString('ficha_clinica')
            ->allowEmptyString('nome_item')
            ->allowEmptyString('categoria')
            ->add('categoria', 'inList', [
                'rule' => ['inList', ['material', 'farmacologico', 'medicamento_vet']],
                'message' => "Categoria deve ser 'material', 'farmacologico', 'medicamento_vet'."
            ]);

        $errors = $validator->validate($data);

        if (!empty($errors)) {
            $mensagens = array_map(fn($e) => implode(', ', $e), $errors);
            throw new BadRequestException(implode(' ', $mensagens));
        }

        $filtros = [
            'data_inicio'    => $data['data_inicio'],
            'data_fim'       => $data['data_fim'],
            'tipo_saida'     => $data['tipo_saida'] ?? null,
            'setor_id'       => $data['setor_id'] ?? null,
            'ficha_clinica'  => $data['ficha_clinica'] ?? null,
            'nome_item'      => $data['nome_item'] ?? null,
            'categoria'      => $data['categoria'] ?? null,
        ];

        $dados = $this->relatorioService->getRelatorioSaidas($filtros);

        $this->response = $this->response->withStatus(200)
            ->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'data' => $dados,
            ]));
    }

    /**
     * @OA\Get(
     *     path="/relatorio/movimentacao",
     *     tags={"Relatórios"},
     *     summary="Relatório de movimentações de entrada",
     *     description="Gera um relatório detalhado das movimentações de entrada, com filtros por subtipo, nome do item, categoria do item e período.",
     *     @OA\Parameter(
     *         name="tipo",
     *         in="query",
     *         required=true,
     *         description="Deve ser 'entrada' para acionar este relatório",
     *         @OA\Schema(type="string", enum={"entrada"})
     *     ),
     *     @OA\Parameter(
     *         name="data_inicio",
     *         in="query",
     *         required=true,
     *         description="Data inicial do período",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="data_fim",
     *         in="query",
     *         required=true,
     *         description="Data final do período",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="subtipo",
     *         in="query",
     *         required=false,
     *         description="Subtipo da movimentação de entrada (ex: compra, devolução, empréstimo)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="nome_item",
     *         in="query",
     *         required=false,
     *         description="Nome do item (busca parcial)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="categoria",
     *         in="query",
     *         required=false,
     *         description="Categoria do item",
     *         @OA\Schema(type="string", enum={"material", "farmacologico", "medicamento_vet"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Relatório gerado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro de validação"
     *     )
     * )
     */
    function relatorioEntradas(): void
    {
        $this->autoRender = false;
        $this->request->allowMethod(['get']);
        $data = $this->request->getQuery();

        $validator = new Validator();
        $validator
            ->requirePresence('data_inicio', 'create', 'A data de início é obrigatória.')
            ->notEmptyDate('data_inicio', 'A data de início não pode estar vazia.')
            ->add('data_inicio', 'formatoData', [
                'rule' => function ($value) {
                    return DateTime::createFromFormat('d-m-Y', $value) !== false;
                },
                'message' => 'Formato inválido. Use Dia-Mes-Ano (ex: 25-06-2024).'
            ])

            ->requirePresence('data_fim', 'create', 'A data de fim é obrigatória.')
            ->notEmptyDate('data_fim', 'A data de fim não pode estar vazia.')
            ->add('data_fim', 'formatoData', [
                'rule' => function ($value) {
                    return DateTime::createFromFormat('d-m-Y', $value) !== false;
                },
                'message' => 'Formato inválido. Use Dia-Mes-Ano (ex: 25-06-2024).'
            ])

            ->allowEmptyString('ficha_clinica')
            ->allowEmptyString('nome_item')
            ->allowEmptyString('categoria')
            ->add('categoria', 'inList', [
                'rule' => ['inList', ['material', 'farmacologico', 'medicamento_vet']],
                'message' => "Categoria deve ser 'material', 'farmacologico', 'medicamento_vet'."
            ]);

        $errors = $validator->validate($data);

        if (!empty($errors)) {
            $mensagens = array_map(fn($e) => implode(', ', $e), $errors);
            throw new BadRequestException(implode(' ', $mensagens));
        }

        $filtros = [
            'data_inicio' => $data['data_inicio'],
            'data_fim'    => $data['data_fim'],
            'subtipo'     => $data['subtipo'] ?? null,
            'nome_item'   => $data['nome_item'] ?? null,
            'categoria'   => $data['categoria'] ?? null,
        ];

        $dados = $this->relatorioService->getRelatorioEntradas($filtros);

        $this->response = $this->response->withStatus(200)
            ->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'data' => $dados,
            ]));
    }

    /**
     * @OA\Get(
     *     path="/relatorio/consumo",
     *     tags={"Relatórios"},
     *     summary="Relatório de estatísticas de consumo",
     *     description="Retorna dados analíticos de consumo de itens, considerando apenas saídas por consumo, com opção de relatório completo.",
     *     @OA\Parameter(name="periodo_inicio", in="query", required=true, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="periodo_fim", in="query", required=true, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="tipo_item", in="query", required=false, @OA\Schema(type="string", enum={"material", "farmacologico", "medicamento_vet"})),
     *     @OA\Parameter(name="unidade", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="completo", in="query", required=false, @OA\Schema(type="boolean")),
     *     @OA\Response(response=200, description="Consulta realizada com sucesso"),
     *     @OA\Response(response=204, description="Nenhum dado encontrado"),
     *     @OA\Response(response=400, description="Parâmetros inválidos")
     * )
     * @throws DateMalformedStringException
     */
    public function estatisticasConsumo(): void
    {
        $this->autoRender = false;
        $this->request->allowMethod(['get']);

        $query = $this->request->getQuery();

        $validator = new Validator();
        $validator
            ->requirePresence('periodo_inicio')
            ->date('periodo_inicio')
            ->requirePresence('periodo_fim')
            ->date('periodo_fim')
            ->allowEmptyString('tipo_item')
            ->add('tipo_item', 'inList', [
                'rule' => ['inList', ['material', 'farmacologico', 'medicamento_vet']],
                'message' => "O parâmetro 'tipo_item' deve ser 'material', 'farmacologico' ou 'medicamento_vet'."
            ])
            ->allowEmptyString('unidade')
            ->add('unidade', 'isString', [
                'rule' => fn($v) => is_string($v),
                'message' => "O parâmetro 'unidade' deve ser uma string."
            ])
            ->requirePresence('completo')
            ->add('completo', 'boolean', [
                'rule' => fn($v) => in_array($v, ['true', 'false', true, false, 0, 1], true),
                'message' => "O parâmetro 'completo' deve ser booleano."
            ]);

        $errors = $validator->validate($query);

        if (!empty($errors)) {
            $mensagens = array_map(fn($e) => implode(', ', $e), $errors);
            throw new BadRequestException(implode(' ', $mensagens));
        }

        $completo = filter_var($query['completo'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $data = $this->relatorioService->getEstatisticasConsumo([
            'periodo_inicio' => $query['periodo_inicio'],
            'periodo_fim' => $query['periodo_fim'],
            'tipo_item' => $query['tipo_item'] ?? null,
            'unidade' => $query['unidade'] ?? null,
            'completo' => $completo
        ]);

        if (empty($data)) {
            $this->response = $this->response->withStatus(204)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'message' => 'Nenhum dado encontrado para os filtros informados.',
                    'data' => []
                ]));
            return;
        }

        $this->response = $this->response->withStatus(200)
            ->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'data' => $data
            ]));
    }

    /**
     * @OA\Get(
     *     path="/relatorio/consumo/export/excel",
     *     tags={"Relatórios"},
     *     summary="Exportar relatório de consumo mês a mês",
     *     description="Exporta um relatório em Excel contendo estatísticas de consumo divididas por mês em abas separadas",
     *     @OA\Parameter(name="periodo_inicio", in="query", required=true, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="periodo_fim", in="query", required=true, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="tipo_item", in="query", required=false, @OA\Schema(type="string", enum={"material", "farmacologico", "medicamento_vet"})),
     *     @OA\Parameter(name="unidade", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Arquivo gerado com sucesso"),
     *     @OA\Response(response=400, description="Parâmetros inválidos"),
     *     @OA\Response(response=422, description="Parâmetros inconsistentes")
     * )
     */
    public function exportarEstatisticasConsumoMensalExcel(): void
    {
        $this->autoRender = false;
        $this->request->allowMethod(['get']);

        $query = $this->request->getQuery();

        $validator = new Validator();
        $validator
            ->requirePresence('periodo_inicio')
            ->add('periodo_inicio', 'formatoValido', [
                'rule' => ['custom', '/^\d{4}-\d{2}-\d{2}$/'],
                'message' => "O campo 'periodo_inicio' deve estar no formato YYYY-MM-DD."
            ])
            ->date('periodo_inicio')
            ->requirePresence('periodo_fim')
            ->add('periodo_fim', 'formatoValido', [
                'rule' => ['custom', '/^\d{4}-\d{2}-\d{2}$/'],
                'message' => "O campo 'periodo_fim' deve estar no formato YYYY-MM-DD."
            ])
            ->date('periodo_fim')
            ->allowEmptyString('tipo_item')
            ->add('tipo_item', 'inList', [
                'rule' => ['inList', ['material', 'farmacologico', 'medicamento_vet']],
                'message' => "O parâmetro 'tipo_item' deve ser 'material', 'farmacologico' ou 'medicamento_vet'."
            ])
            ->allowEmptyString('unidade')
            ->add('unidade', 'isString', [
                'rule' => fn($v) => is_string($v),
                'message' => "O parâmetro 'unidade' deve ser uma string."
            ]);

        $errors = $validator->validate($query);

        if (!empty($errors)) {
            $mensagens = array_map(fn($e) => implode(', ', $e), $errors);
            throw new BadRequestException(implode(' ', $mensagens));
        }

        $filtros = [
            'periodo_inicio' => $query['periodo_inicio'],
            'periodo_fim' => $query['periodo_fim'],
            'tipo_item' => $query['tipo_item'] ?? null,
            'unidade' => $query['unidade'] ?? null,
            'completo' => true
        ];

        try {
            $arquivo = $this->relatorioService->exportarEstatisticasConsumoMensalParaExcel($filtros);

            $this->response = $this->response
                ->withType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->withDownload('relatorio_consumo_mensal.xlsx')
                ->withFile($arquivo, ['delete' => true]);

        } catch (
        DateMalformedStringException |
        \PhpOffice\PhpSpreadsheet\Writer\Exception |
        \PhpOffice\PhpSpreadsheet\Exception |
        Exception $e
        ) {
            $this->response = $this->response->withStatus(500)
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Erro ao gerar o relatório: ' . $e->getMessage()
                ]));
        }
    }
}
