<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\MovimentacaoService;
use App\Test\Fixture\FornecedorFixture;
use App\Test\Fixture\ItemFixture;
use App\Test\Fixture\LoteFixture;
use App\Test\Fixture\LoteMovimentacaoFixture;
use App\Test\Fixture\MovimentacaoFixture;
use App\Test\Fixture\SetorFixture;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Text;
use TypeError;

class MovimentacaoServiceTest extends TestCase
{
    public array $fixtures = [
        ItemFixture::class,
        SetorFixture::class,
        FornecedorFixture::class,
        MovimentacaoFixture::class,
        LoteFixture::class,
        LoteMovimentacaoFixture::class,
    
    ];

    private MovimentacaoService $movimentacaoService;

    public function setUp(): void
    {
        parent::setUp();
        $movimentacaoTable = TableRegistry::getTableLocator()->get('Movimentacao');
        $this->movimentacaoService = new MovimentacaoService($movimentacaoTable);
    }

    public function tearDown(): void
    {
        unset($this->movimentacaoService);
        parent::tearDown();
    }

    /** @dataProvider dataProviderEntrada */
    public function testRegistrarEntrada(string $subtipo): void
    {
        $data = [
            'tipo_movimentacao' => 'entrada',
            'subtipo_movimentacao' => $subtipo,
            'data' => date('Y-m-d'),
            'fornecedor_id' => 1,
            'via_compra' => 'online',
            'documento_origem' => 'DOC123',
            'setor_id' => 1,
            'lote' => [[
                "item_id"=> 1,
                "numero_lote"=> "LOTE124",
                "data_vencimento"=> "2025-05-30",
                "data_de_recebimento"=> "2025-05-28",
                "quantidade"=> 123,
                "valor_unitario"=> 1,
                "valor_total"=> 123,
                "is_ativo"=> true
            ]]
        ];

        $result = $this->movimentacaoService->registrarMovimentacao($data);

        $this->assertTrue($result['success']);
        $this->assertEquals('entrada', $result['data']->tipo_movimentacao);
        $this->assertEquals($subtipo, $result['data']->subtipo_movimentacao);
    }

    public static function dataProviderEntrada(): array
    {
        return [['compra'], ['devolucao'], ['emprestimo']];
    }

    public function testRegistrarPerdaComSucesso(): void
    {
        $data = [
            'tipo_movimentacao' => 'saida',
            'subtipo_movimentacao' => 'perda',
            'data' => date('Y-m-d'),
            'observacao' => 'Item danificado',
            'lote' => [
                [
                'item_id' => 2,
                'quantidade' => 1,
                'lote_id' => 2,
                ],
            ]
        ];

        $result = $this->movimentacaoService->registrarMovimentacao($data);
        $this->assertTrue($result['success']);
    }

    public function testRegistrarVencimentoComSucesso(): void
    {
        $data = [
            'tipo_movimentacao' => 'saida',
            'subtipo_movimentacao' => 'vencimento',
            'data' => date('Y-m-d'),
            'observacao' => 'Produto vencido',
            'lote' => [[
                'lote_id' => 1
            ]]
        ];

        $result = $this->movimentacaoService->registrarMovimentacao($data);
        $this->assertTrue($result['success']);
    }

    public function testRegistrarSaidaItemComSucesso(): void
    {
        $data = [
            'tipo_movimentacao' => 'saida',
            'subtipo_movimentacao' => 'consumo famez',
            'data' => date('Y-m-d'),
            'nome_animal' => 'Rex',
            'setor' => '001',
            'setor' => '001',
            'requerimento' => 'REQ001',
            'ficha_clinica' => 'FICHA001',
            'requerente' => 'Dr. João',
            'observacao' => 'Atendimento clínico',
            'lote' => [[
                'item_id' => 1,
                'item_id' => 1,
                'quantidade' => 1
            ]]
        ];

        $result = $this->movimentacaoService->registrarMovimentacao($data);
        $this->assertTrue($result['success']);
    }

    public function testRegistrarMovimentacaoComCamposInvalidos(): void
    {
        $data = [
            'tipo_movimentacao' => '',
            'subtipo_movimentacao' => '',
        ];

        $result = $this->movimentacaoService->registrarMovimentacao($data);
        $this->assertFalse($result['success']);
        $this->assertEquals('Tipo e subtipo são obrigatórios.', $result['message']);
    }

    public function testRegistrarMovimentacaoComTipoDesconhecido(): void
    {
        $data = [
            'tipo_movimentacao' => 'desconhecido',
            'subtipo_movimentacao' => 'qualquer'
        ];

        $result = $this->movimentacaoService->registrarMovimentacao($data);
        $this->assertFalse($result['success']);
        $this->assertEquals('Tipo ou subtipo não reconhecido.', $result['message']);
    }

    public function testListarLoteMovimentacao()
    {
        $movimentacao = TableRegistry::getTableLocator()->get('Movimentacao')->find()->contain(['LotesMovimentacoes', 'Lote'])->first();
        $this->assertNotEmpty($movimentacao, 'Nenhuma movimentação encontrada no banco de dados.');

        $movimentacaoId = $movimentacao->id;

        $result = $this->movimentacaoService->getMovimentacoesByLoteId($movimentacaoId);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertEquals(5, $result[0]['movimentacao_id']);
        $this->assertEquals('saida', $result[0]['tipo_movimentacao']);
        $this->assertEquals('consumo famez', $result[0]['subtipo_movimentacao']);
        $this->assertArrayHasKey('lote', $result[0]);
    }
    
    public function testListarLoteMovimentacaoComLoteInexistente()
    {
        $result = $this->movimentacaoService->getMovimentacoesByLoteId(9999);
        $this->assertEmpty($result['success'], 'Deveria retornar um array vazio para lote inexistente.');
    }
    public function testMovimentacaoIdNegativoOuZero()
    {
        $resultNegativo = $this->movimentacaoService->getMovimentacoesByLoteId(-1);
        $this->assertIsArray($resultNegativo);
        $this->assertEmpty($resultNegativo['success'], 'Esperado resultado vazio para ID negativo');

        $resultZero = $this->movimentacaoService->getMovimentacoesByLoteId(0);
        $this->assertIsArray($resultZero);
        $this->assertEmpty($resultZero['success'], 'Esperado resultado vazio para ID zero');
    }

    public function testMovimentacaoIdTipoInvalido()
    {
        $this->expectException(TypeError::class);
        $this->movimentacaoService->getMovimentacoesByLoteId("abc");

        $this->expectException(TypeError::class);
        $this->movimentacaoService->getMovimentacoesByLoteId(true);

        $this->expectException(TypeError::class);
        $this->movimentacaoService->getMovimentacoesByLoteId([]);
    }
    public function testGetMovimentacaoDetalhadaByIdExaustivo()
    {

        for ($id = 1; $id < 120; $id++) {
            $result = $this->movimentacaoService->getMovimentacaoDetalhadaById($id);

            $this->assertNotNull($result, "A movimentação ID $id deve existir.");

            $camposEsperados = [
                'id',
                'tipo_movimentacao',
                'subtipo_movimentacao',
                'data',
                'setor',
                'fornecedor',
                'lotes'
            ];

            foreach ($camposEsperados as $campo) {
                $this->assertArrayHasKey($campo, $result, "Campo '$campo' está ausente no resultado da movimentação ID $id.");

                switch ($campo) {
                    case 'id':
                        $this->assertIsInt($result[$campo], "ID deve ser um inteiro na movimentação ID $id.");
                        $this->assertEquals($id, $result[$campo], "ID da movimentação não corresponde.");
                        break;

                    case 'tipo_movimentacao':
                    case 'subtipo_movimentacao':
                        $this->assertIsString($result[$campo], "Campo '$campo' deve ser uma string na movimentação ID $id.");
                        $this->assertNotEmpty($result[$campo], "Campo '$campo' não deve estar vazio na movimentação ID $id.");
                        break;

                    case 'fornecedor':
                        if ($result[$campo] !== null) {
                            $this->assertNotEmpty($result[$campo], "Campo '$campo' não deve estar vazio na movimentação ID $id.");
                        }
                        break;

                    case 'lotes':
                        $this->assertIsArray($result[$campo], "Campo 'lotes' deve ser um array na movimentação ID $id.");
                        foreach ($result[$campo] as $lote) {   
                            $this->assertIsObject($lote, "Cada item em 'lotes' deve ser um array na movimentação ID $id.");
                            $this->assertArrayHasKey('id', $lote, "Lote deve ter atributo 'id' na movimentação ID $id.");
                            $this->assertIsInt($lote['id'], "ID do lote deve ser inteiro na movimentação ID $id.");
                            $this->assertArrayHasKey('numero_lote', $lote, "Lote deve ter 'numero_lote' na movimentação ID $id.");
                            $this->assertIsString($lote['numero_lote'], "Número do lote deve ser string na movimentação ID $id.");
                        }
                        break;
                }
            }
        }
    }

}