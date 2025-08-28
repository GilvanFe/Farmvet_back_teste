<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\MovimentacaoController;
use App\Test\Fixture\CatmatFixture;
use App\Test\Fixture\ItemFixture;
use App\Test\Fixture\MovimentacaoFixture;
use App\Test\Fixture\FornecedorFixture;
use App\Test\Fixture\LoteFixture;
use App\Test\Fixture\LoteMovimentacaoFixture;
use App\Test\Fixture\SetorFixture;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\MovimentacaoController Test Case
 *
 * @uses \App\Controller\MovimentacaoController
 */
class MovimentacaoControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        
        ItemFixture::class,
        SetorFixture::class,
        FornecedorFixture::class,
        MovimentacaoFixture::class,
        LoteFixture::class,
        LoteMovimentacaoFixture::class,
    ];

    public function testSearchMovimentacaoVencimento(): void
    {
        $this->get('/movimentacoes/vencimento/search');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('pagination', $response);

        $this->assertEquals(4, $response['data'][0]['id']);
        $this->assertEquals('2025-05-03', $response['data'][0]['data']);
        $this->assertEquals('saida', $response['data'][0]['tipo_movimentacao']);
        $this->assertEquals('vencimento', $response['data'][0]['subtipo_movimentacao']);

        $this->assertEquals(1, $response['pagination']['current_page']);
        $this->assertEquals(1, $response['pagination']['total_pages']);
        $this->assertEquals(1, $response['pagination']['total_movimentacao']);
        $this->assertEquals(60, $response['pagination']['limit']);
    }

    public function testSearchMovimentacaoPerda(): void
    {
        $this->get('/movimentacoes/perda/search');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('pagination', $response);

        $this->assertEquals(3, $response['data'][0]['id']);
        $this->assertEquals('2025-05-03', $response['data'][0]['data']);
        $this->assertEquals('saida', $response['data'][0]['tipo_movimentacao']);
        $this->assertEquals('perda', $response['data'][0]['subtipo_movimentacao']);

        $this->assertEquals(1, $response['pagination']['current_page']);
        $this->assertEquals(1, $response['pagination']['total_pages']);
        $this->assertEquals(1, $response['pagination']['total_movimentacao']);
        $this->assertEquals(60, $response['pagination']['limit']);
    }

    public function testSearchMovimentacaoEntrada(): void
    {
        $this->get('/movimentacoes/entrada/search');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('pagination', $response);

        $this->assertEquals(1, $response['data'][0]['id']);
        $this->assertEquals('2025-05-01', $response['data'][0]['data']);
        $this->assertEquals('entrada', $response['data'][0]['tipo_movimentacao']);
        $this->assertEquals('compra', $response['data'][0]['subtipo_movimentacao']);

        $this->assertEquals(1, $response['pagination']['current_page']);
        $this->assertEquals(2, $response['pagination']['total_pages']);
        $this->assertEquals(60, $response['pagination']['limit']);
    }
    public function testSearchMovimentacaoSaida(): void
    {
        $this->get('/movimentacoes/saida/search');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('pagination', $response);

        $this->assertEquals(5, $response['data'][0]['id']);
        $this->assertEquals(date('Y-m-d'), $response['data'][0]['data']);
        $this->assertEquals('saida', $response['data'][0]['tipo_movimentacao']);
        $this->assertEquals('consumo famez', $response['data'][0]['subtipo_movimentacao']);

        $this->assertEquals(1, $response['pagination']['current_page']);
        $this->assertEquals(1, $response['pagination']['total_pages']);
        $this->assertEquals(60, $response['pagination']['total_movimentacao']);
        $this->assertEquals(60, $response['pagination']['limit']);
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\MovimentacaoController::view()
     */
    public function testView(): void
    {
        $movimentacaoId = 50;
        $this->get("/movimentacao/lote/{$movimentacaoId}");
        $this->assertResponseOk();
        $responseBody = (string)$this->_response->getBody();
        $responseData = json_decode($responseBody, true);

        $expectedPayload = [
            "data" => [
                [
                    "movimentacao_id" => 50,
                    "tipo_movimentacao" => "entrada",
                    "subtipo_movimentacao" => "compra",
                    "data" => date('Y-m-d', strtotime("-45 days")),
                    "quantidade" => 10005,
                    "lote" => [
                        "id" => 46,
                        "data_vencimento" => '2025-06-30',
                        "data_de_recebimento" => '2025-05-03',
                        "quantidade" => 180,
                        "valor_unitario" => 1.5,
                        "valor_total" => 45,
                        "item_id" => 2,
                        "numero_lote" => "TEX0042",
                        "is_ativo" => true,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedPayload, $responseData);
    }

    /**
     * Test add method
     *
     * @return void
     * @uses \App\Controller\MovimentacaoController::add()
     */
    public function testAdd(): void
    {
        $movimentacaoData = [
            "tipo_movimentacao"=> "entrada",
            "subtipo_movimentacao"=> "compra",
            'item_id'=> '1',
            "data"=> date('Y-m-d'),
            "fornecedor_id"=> "1",
            "via_compra"=> "31231",
            "documento_origem"=> "3412421",
            "lote"=> [
                [
                    "item_id"=> 1,
                    "data_vencimento"=> "2026-05-12",
                    "data_de_recebimento"=> "2025-05-12",
                    "quantidade"=> 5,
                    "valor_unitario"=> 10.5,
                    "valor_total"=> 52.5,
                    "numero_lote"=> "L20250512-A",
                    "is_ativo"=> true
                ],
                [
                    "item_id"=> 2,
                    "data_vencimento"=> "2026-05-12",
                    "data_de_recebimento"=> "2025-05-12",
                    "quantidade"=> 5,
                    "valor_unitario"=> 10.5,
                    "valor_total"=> 52.5,
                    "numero_lote"=> "L20250512-B",
                    "is_ativo"=> true
                ],
            ]

        ];
        
        $this->post('/movimentacao/add', $movimentacaoData);

        $responseBody = (string) $this->_response->getBody();
        $responseJson = json_decode($responseBody, true);

        $this->assertArrayHasKey("status", $responseJson);
        $this->assertEquals("success", $responseJson['status']);
        $this->assertArrayHasKey("message", $responseJson);
        $this->assertEquals("Movimentação de entrada registrada com sucesso.", $responseJson['message']);

        $movimentacoes = \Cake\ORM\TableRegistry::getTableLocator()->get('Movimentacao');
        $query = $movimentacoes->find()->where(['tipo_movimentacao' => 'entrada', 'fornecedor_id' => 1]);
        $this->assertGreaterThan(1, $query->count());
    }


}
