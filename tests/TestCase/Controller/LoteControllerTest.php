<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\LoteController;
use App\Test\Fixture\FornecedorFixture;
use App\Test\Fixture\ItemFixture;
use App\Test\Fixture\LoteFixture;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\Datasource\Exception\RecordNotFoundException;
use InvalidArgumentException;
use Cake\Http\Exception\NotFoundException;

/**
 * App\Controller\LoteController Test Case
 *
 * @uses \App\Controller\LoteController
 */
class LoteControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        LoteFixture::class,
        FornecedorFixture::class,
        ItemFixture::class
    ];

    /**
     * Testa o caminho feliz: paginação padrão.
     */
    public function testIndexSuccess(): void
    {
        $this->get('/lote/index?page=1');
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertIsArray($response, 'Resposta deve ser um array de lotes');
        $totalLotes = $this->getTableLocator()->get('Lote')->find()->count();
        $limit = 20;

        $expectedCount = min($totalLotes, $limit);
        $this->assertCount($expectedCount, $response);
    }

    /**
     * Testa página inválida (zero ou negativa): deve tratar como página 1.
     */
    public function testIndexInvalidPageZeroOrNegative(): void
    {
        $this->get('/lote/index?page=0');
        $responseZero = json_decode((string)$this->_response->getBody(), true);

        $this->get('/lote/index?page=-1');
        $responseNegative = json_decode((string)$this->_response->getBody(), true);

        $this->get('/lote/index?page=1');
        $responsePageOne = json_decode((string)$this->_response->getBody(), true);

        $this->assertEquals($responsePageOne, $responseZero, 'Página 0 deve ser tratada como página 1');
        $this->assertEquals($responsePageOne, $responseNegative, 'Página negativa deve ser tratada como página 1');
    }

    /**
     * Testa parâmetros inválidos de página e limite (exemplo: string).
     */
    public function testIndexInvalidPageAndLimitParams(): void
    {
        $this->get('/lote/index?page=abc&limit=xyz');
        $this->assertResponseOk();

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertIsArray($response, 'Resposta deve ser array mesmo com parâmetros inválidos');
    }
    
    /**
     * Teste caminho feliz: cria um lote com sucesso.
     */
    public function testAddSuccess(): void
    {
        $postData = [
            'item_id' => 1,
            'numero_lote' => 'L123',
            'data_vencimento' => date('Y-m-d', strtotime('+31 days')),
            'quantidade_atual' => 20,
            'valor_unitario' => 5.5,
            'quantidade' => 20,
            'data_de_recebimento' => date('Y-m-d'),
        ];

        $this->post('/lote/add', $postData);
        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertResponseCode(201);
        $this->assertContentType('application/json');

        
        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success'], 'Esperado sucesso na criação do lote');

        $lotes = $this->getTableLocator()->get('Lote')->find()->where(['numero_lote' => 'L123'])->all();
        $this->assertNotEmpty($lotes, 'Deveria ter um lote com o número enviado');
    }

    /**
     * Testa tentativa de criação de lote com dados inválidos (ex: falta de campos obrigatórios).
     */
    public function testAddValidationError(): void
    {
        $postData = [
            'item_id' => 1,
            'numero_lote' => '',
            'data_vencimento' => '',
            'quantidade_atual' => -5
        ];

        $this->post('/lote/add', $postData);

        $this->assertResponseCode(400);
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertArrayHasKey('success', $response);
        $this->assertFalse($response['success'], 'Deveria retornar erro por dados inválidos');
        $this->assertArrayHasKey('message', $response);
        $this->assertNotEmpty($response['message'], 'Mensagem de erro deveria estar presente');
    }

    /**
     * Testa envio de método não permitido (exemplo: GET ao invés de POST).
     */
    public function testAddInvalidMethod(): void
    {
        $this->get('/lote/add');

        $this->assertResponseCode(405);
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Método de requisição inválido', $response['message']);
    }

    public function testPorItemSuccess(): void
    {
        $this->get('/lote/item/1');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertTrue($response['success'], 'Deveria retornar sucesso');
        $this->assertArrayHasKey('data', $response, 'Resposta deveria conter o campo "data"');
        $this->assertIsArray($response['data'], 'O campo "data" deve ser uma lista de lotes');
        $this->assertGreaterThan(0, count($response['data']), 'Deveria retornar ao menos um lote');
    }

    public function testPorItemNotFound(): void
    {
        $this->get('/lotes/item/9999');

        $this->assertResponseCode(404);
    }

    public function testPorItemInvalidMethod(): void
    {
        $this->post('/lote/item/1');

        $this->assertResponseCode(405);
    }

    public function testPorItemNonNumericId(): void
    {
        $this->get('/lotes/item/abc');

        $this->assertResponseCode(404);
    }

    public function testEditSuccess(): void
    {
        $postData = [
            'numero_lote' => 'L999',
            'data_de_recebimento' => date('Y-m-d'),
            'data_vencimento' => date('Y-m-d', strtotime('+30 days')),
            'quantidade' => 10,
            'valor_unitario' => 5.5,
        ];

        $this->patch('/lote/edit/1', $postData);

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertResponseCode(200);
        $this->assertContentType('application/json');

        

        $this->assertTrue($response['success']);
        $this->assertEquals('Lote atualizado com sucesso.', $response['message']);
        $this->assertArrayHasKey('data', $response);
    }

    public function testEditFailsWithInvalidMethod(): void
    {
        $this->get('/lote/edit/1');

        $this->assertResponseCode(405);

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Método de requisição inválido', $response['message']);
    }

    public function testEditFailsWhenLoteNotFound(): void
    {
        $postData = [
            'numero_lote' => 'L000',
            'data_de_recebimento' => date('Y-m-d'),
            'data_vencimento' => date('Y-m-d', strtotime('+30 days')),
            'quantidade' => 5,
            'valor_unitario' => 2.0,
        ];

        $this->patch('/lote/edit/9999', $postData);

        $this->assertResponseCode(400);

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertFalse($response['success']);
        $this->assertEquals('Lote não encontrado.', $response['message']);
    }

    public function testEditFailsWithDateValidation(): void
    {
        $postData = [
            'numero_lote' => 'L002',
            'data_de_recebimento' => date('Y-m-d', strtotime('+30 days')),
            'data_vencimento' => date('Y-m-d'),
            'quantidade' => 5,
            'valor_unitario' => 2.0,
        ];

        $this->patch('/lote/edit/1', $postData);

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertResponseCode(400);

        $this->assertFalse($response['success']);
        $this->assertEquals('A data de vencimento não pode ser menor que a data de recebimento.', $response['message']);
    }

    public function testEditFailsOnSaveError(): void
    {
        $postData = [
            'numero_lote' => '',
            'data_de_recebimento' => date('Y-m-d'),
            'data_vencimento' => date('Y-m-d', strtotime('+30 days')),
        ];

        $this->patch('/lote/edit/1', $postData);

        $this->assertResponseCode(400);

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertFalse($response['success']);
        $this->assertEquals('Erro ao atualizar o lote.', $response['message']);
        $this->assertArrayHasKey('errors', $response);
    }

    public function testBuscarSuccess(): void
    {
        $id = 1;

        $this->get("/lote/buscar/{$id}");

        $this->assertResponseOk();

        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertEquals('success', $response['status']);

        $this->assertNotEmpty($response['data']);

        $this->assertNull($response['message']);
    }

    public function testCalcularDebitoSuccessPartialAndFull(): void
    {
        $itemId = 1;
        $quantidade = 15;

        $this->get("/lote/calcularDebito/{$itemId}/{$quantidade}");

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertTrue($response['success']);
        $this->assertEquals('Cálculo de débito realizado com sucesso.', $response['message']);
        $this->assertIsArray($response['data']);
        $this->assertNotEmpty($response['data']);

        $totalDebit = array_sum(array_column($response['data'], 'quantidade_debitada'));
        $this->assertEquals($quantidade, $totalDebit);
    }

    public function testCalcularDebitoFailsNoLotes(): void
    {
        $itemId = 999999;

        $this->get("/lote/calcularDebito/{$itemId}/10");

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('Não existem lotes para o ítem ID', $response['message']);
        $this->assertEmpty($response['data']);
    }

    public function testCalcularDebitoFailsNoLotesAtivos(): void
    {
        $itemId = 3;

        $this->get("/lote/calcularDebito/{$itemId}/5");

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('Não existem lotes para o ítem ID 3.', $response['message']);
        $this->assertEmpty($response['data']);
    }

    public function testCalcularDebitoQuantityExceedsAvailable(): void
    {
        $itemId = 1;
        $quantidade = 999999;

        $this->get("/lote/calcularDebito/{$itemId}/{$quantidade}");

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertTrue($response['success']);
        $this->assertEquals('Cálculo de débito realizado com sucesso.', $response['message']);
        $this->assertIsArray($response['data']);
        $this->assertNotEmpty($response['data']);

        $totalDebit = array_sum(array_column($response['data'], 'quantidade_debitada'));
        $this->assertLessThanOrEqual($quantidade, $totalDebit);
    }

    public function testVerificarEstoqueMinimoItensAbaixoDoMinimo(): void
    {
        $this->get('/lote//verificar-estoque-minimo');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertEquals('warning', $response['status']);
        $this->assertEquals('Itens com estoque abaixo do mínimo.', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertIsArray($response['data']);
        $this->assertNotEmpty($response['data']);

        foreach ($response['data'] as $itemResult) {
            $this->assertArrayHasKey('item_id', $itemResult);
            $this->assertArrayHasKey('nome', $itemResult);
            $this->assertArrayHasKey('estoque_minimo', $itemResult);
            $this->assertArrayHasKey('estoque_restante', $itemResult);
            $this->assertLessThan($itemResult['estoque_minimo'], $itemResult['estoque_restante'], 'Estoque restante deveria estar abaixo do mínimo');
        }
    }
    public function testGetValorTotalDeTodosOsLotesSuccess(): void
    {
        $this->get('/lote/getValorTotalDeTodosOsLotes');

        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertArrayHasKey('valor_total', $response, 'Deve conter a chave "valor_total"');
        $this->assertIsNumeric($response['valor_total'], 'Valor total deve ser numérico');
        $this->assertGreaterThanOrEqual(0, $response['valor_total'], 'Valor total deve ser >= 0');
    }

    public function testLotesProximosVencimentoSuccess(): void
    {
        $this->get('/lote/proximos-vencimento');

        $this->assertResponseOk();
        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertTrue($response['success']);
        $this->assertIsArray($response['data']);
        if (!empty($response['data'])) {
            $this->assertArrayHasKey('item', $response['data'][0]);
            $this->assertArrayHasKey('lote', $response['data'][0]);
            $this->assertArrayHasKey('validade', $response['data'][0]);
        }
    }
}
