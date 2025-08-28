<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\LoteService;
use App\Model\Table\LoteTable;
use App\Test\Fixture\FornecedorFixture;
use App\Test\Fixture\ItemFixture;
use App\Test\Fixture\LoteFixture;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;

class LoteServiceTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array<string>
     */
    public array $fixtures = [
        FornecedorFixture::class,
        ItemFixture::class,
        LoteFixture::class
    ];

    private LoteService $loteService;
    private LoteTable $loteTable;

    public function setUp(): void
    {
        parent::setUp();
        // Obtendo a instância da tabela Lote
        $this->loteTable = TableRegistry::getTableLocator()->get('Lote');
        // Inicializando o serviço com a tabela
        $this->loteService = new LoteService($this->loteTable);
    }

    public function tearDown(): void
    {
        unset($this->loteService);
        parent::tearDown();
    }

    /**
     * Teste para criar um lote com sucesso
     */
    public function testCreateLoteSuccess(): void
    {
        $data = [
            'numero_lote'=> 234,
            'data_vencimento' => '2025-10-08',
            'data_de_recebimento' => '2024-10-08',
            'quantidade' => 100,
            'valor_unitario' => 15.00,
            'via_compra' => 'compra online',
            'documento_de_origem' => 'nota fiscal 123',
            'fornecedor_id' => 2,
            'item_id' => 2,
            'tipo_entrada' => 'Entrada'
        ];

        $result = $this->loteService->createLote($data);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals('O lote foi salvo com sucesso.', $result['message']);
        $this->assertNotEmpty($result['data']->id);
    }


    public function testGetLotesByItemIdComResultados(): void
    {
        $itemId = 1;
        $lotesEsperados = 2;

        $query = $this->loteService->getLotesByItemId($itemId);

        $this->assertInstanceOf(\Cake\ORM\Query::class, $query);
        $this->assertEquals($lotesEsperados, $query->count());

        $primeiroLote = $query->first();
        $this->assertEquals($itemId, $primeiroLote->item_id);
    }

    /**
     * Testa o caso de um item_id que não existe na base de dados.
     */
    public function testGetLotesByItemIdInexistente(): void
    {
        $itemId = 999;

        $query = $this->loteService->getLotesByItemId($itemId);

        $this->assertInstanceOf(\Cake\ORM\Query::class, $query);
        $this->assertEquals(0, $query->count());
    }



    public function testMultipleActiveLotForItemId()
    {
        $lotesAtivos = $this->loteTable->find()->where(['item_id' => 2, 'is_ativo' => true])->count();


        $this->assertGreaterThanOrEqual(1, $lotesAtivos, 'Deve haver exatamente um lote ativo para o item_id = 2.');
    }

    public function verificarLotesVencidosAtivos(): array
    {

        $lotesVencidos = $this->loteTable->find()
            ->where(['is_ativo' => true, 'data_vencimento <' => date('Y-m-d')])
            ->all();

        if ($lotesVencidos->isEmpty()) {
            return [
                'success' => true,
                'message' => 'Não existem lotes vencidos ativos.',
                'data' => []
            ];
        }

        $lotesVencidosData = [];
        foreach ($lotesVencidos as $lote) {
            $lotesVencidosData[] = [
                'id' => $lote->id,
                'numero_lote' => $lote->numero_lote,
                'data_vencimento' => $lote->data_vencimento,
            ];
        }

        return [
            'success' => false,
            'message' => 'Existem lotes vencidos ativos.',
            'data' => $lotesVencidosData
        ];
    }


    public function testVerificarEstoqueMinimoParaTodosItens(): void
    {
        $resultado = $this->loteService->verificarEstoqueMinimoParaTodosItens();
        
        $this->assertEquals('warning', $resultado['status']);

        $this->assertCount(1, $resultado['data']);
        $this->assertEquals(1, $resultado['data'][0]['item_id']);

        $this->assertEquals('insulina', $resultado['data'][0]['nome']);
        
        $this->assertEquals(200000, $resultado['data'][0]['estoque_minimo']);
        $this->assertEquals(349, $resultado['data'][0]['estoque_restante']);
    }


}
