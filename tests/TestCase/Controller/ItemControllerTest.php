<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\ItemController;
use App\Test\Fixture\ItemFixture;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\ItemController Test Case
 *
 * @uses \App\Controller\ItemController
 */
class ItemControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        ItemFixture::class
    ];

    /**
     * Test index method
     *
     * @return void
     * @uses \App\Controller\ItemController::index()
     */
    public function testIndex(): void
    {
        $this->get('/item/listagem');


        $this->assertResponseOk();


        $this->assertContentType('application/json');


        $this->assertJsonResponse();

    }

    protected function assertJsonResponse(): void
    {
        $responseBody = json_decode((string)$this->_response->getBody(), true);
        $this->assertIsArray($responseBody, 'Expected response to be an array');
    }

/**
     * Test index method
     *
     * @return void
     * @uses \App\Controller\ItemController::index()
     */

     public function testIndexPagination(): void
     {
        $this->get('/item/listagem?page=1');
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $responseBody = json_decode((string)$this->_response->getBody(), true);
        $this->assertIsArray($responseBody);
        $totalItems = $this->getTableLocator()->get('Item')->find()->count();
        $totalExpectedOnPage1 = min(60, $totalItems);

        $this->assertCount($totalItems, $responseBody, 'Expected is different than the total expected items for page 1');

        $this->get('/item/listagem?page=3');
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $responseBody = json_decode((string)$this->_response->getBody(), true);
        $this->assertIsArray($responseBody);

        $this->assertCount(0, $responseBody, 'Expected 0 items for page 3');
     }


    /**
     * Testa a action view() com um ID existente.
     * Este é o "caminho feliz".
     */
    public function testViewSuccessWhenItemExists(): void
    {
        $this->get('/item/view/1');
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $expectedResponse = [
            'status' => 'success',
            'data' => [
                'id' => 1,
                'nome' => 'insulina',
                'tipo_item' => 'medicamento_vet',
                'estoque_minimo' => 200000,
                'is_ativo' => true,
                'is_controlado' => false,
                'descricao_completa' => null,
                'descricao_complementar' => null,
                'unidade' => 'ml',
                'observacao' => null,
                'legislacao_especifica' => null,
                'codigo_catmat' => null,
            ],
            'message' => null,
        ];

        foreach ($expectedResponse as $key => $expectedValue) {
            $this->assertArrayHasKey($key, $response, "Faltando chave '$key' na resposta");
            if ($key === 'data') {
                foreach ($expectedResponse['data'] as $field => $expectedFieldValue) {
                    $this->assertArrayHasKey($field, $response['data'], "Faltando campo '$field' em data");
                    $this->assertSame($expectedFieldValue, $response['data'][$field], "Valor inesperado em '$field'");
                }
            } else {
                $this->assertSame($expectedValue, $response[$key], "Valor inesperado em '$key'");
            }
        }
    }

    /**
     * Testa a action view() quando o item solicitado não existe.
     * Este é o primeiro "limite".
     */
    public function testViewReturnsErrorWhenItemDoesNotExist(): void
    {
        $this->get('/item/view/999');
        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertContentType('application/json');
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('message', $response);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Item não encontrado.', $response['message']);
    }

    /**
     * Testa a action view() com um formato de ID inválido (não numérico).
     * Este é o segundo "limite".
     */
    public function testViewReturnsErrorForInvalidIdFormat(): void
    {
        $this->get('/item/view/abc');
        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertContentType('application/json');
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('message', $response);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Item não encontrado.', $response['message']);
    }

    public function testSearchItemsSuccessAndLimit(): void
    {
        $this->get('/item/searchItems?query=insulina');
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertIsArray($response, 'Resposta da busca deveria ser um array');

        $totalItemsMatching = $this->getTableLocator()->get('Item')->find()
            ->where(['nome LIKE' => '%insulina%'])
            ->count();

        $this->assertLessThanOrEqual(60, count($response), 'Deveria retornar no máximo 60 itens');
        $this->assertCount(min(60, $totalItemsMatching), $response, 'Quantidade inesperada de itens retornados');
    }

    /**
     * Testa a action searchItems() com um termo que não retorna resultados.
     */
    public function testSearchItemsNoResults(): void
    {
        $this->get('/item/searchItems?query=xxxxxxx_inexistente');
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertIsArray($response, 'Resposta da busca deveria ser um array');
        $this->assertEmpty($response, 'Deveria retornar um array vazio quando não há resultados');
    }

    /**
     * Testa a action searchItems() com termo de busca vazio.
     */
    public function testSearchItemsEmptyQuery(): void
    {
        $this->get('/item/searchItems?query=');
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertIsArray($response, 'Resposta da busca deveria ser um array');

        $totalItems = $this->getTableLocator()->get('Item')->find()->count();

        $this->assertLessThanOrEqual(60, count($response), 'Mesmo com busca vazia, deve retornar no máximo 60 itens');
        $this->assertCount(min(60, $totalItems), $response, 'Quantidade inesperada de itens com busca vazia');
    }

    /**
     * Testa a action add() com sucesso (POST válido com dados corretos).
     */
    public function testAddSuccess(): void
    {
        $postData = [
            'nome' => 'Novo Item',
            'tipo_item' => 'medicamento_vet',
            'estoque_minimo' => 100,
            'unidade' => 'ml',
            'codigo_catmat' => '123456',
            'is_ativo' => true,
            'is_controlado' => false,
            'descricao_completa' => 'Descrição completa do novo item',
            'unidade' => 'AM 1.00 ML',
        ];

        $this->post('/item/add', $postData);
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('success', $response['status'], 'Deveria retornar status success ao criar item');

        $item = $this->getTableLocator()->get('Item')->find()->where(['nome' => 'Novo Item'])->first();
        $this->assertNotEmpty($item, 'O item deveria ter sido salvo no banco');
    }

    /**
     * Testa a action add() com método GET (não permitido).
     */
    public function testAddInvalidMethod(): void
    {
        $this->get('/item/add');
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Invalid request method.', $response['message']);
    }

    /**
     * Testa a action add() com POST mas sem os dados obrigatórios.
     * Limite: Falha por falta de campos obrigatórios.
     */
    public function testAddMissingRequiredFields(): void
    {
        $this->post('/item/add', []);
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('error', $response['status']);
        $this->assertNotEmpty($response['message'], 'Deveria ter uma mensagem de erro ao faltar campos obrigatórios');
    }

    /**
     * Testa o softDelete com sucesso (caminho feliz usando PATCH).
     */
    public function testSoftDeleteSuccess(): void
    {
        $this->patch('/item/softDelete/1');
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('success', $response['status'], 'Soft delete deveria retornar sucesso');

        $item = $this->getTableLocator()->get('Item')->get(1);
        $this->assertFalse($item->is_ativo, 'Item deveria estar marcado como inativo após soft delete');
    }

    /**
     * Testa o softDelete com ID inexistente.
     */
    public function testSoftDeleteNonExistentId(): void
    {
        $this->patch('/item/softDelete/9999');
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Item not found.', $response['message']);
    }

    /**
     * Testa edição bem-sucedida de um item existente.
     */
    public function testEditSuccess(): void
    {
        $postData = [
            'nome' => 'Editar Item',
            'tipo_item' => 'medicamento_vet',
            'estoque_minimo' => 100,
            'unidade' => 'ml',
            'codigo_catmat' => '123456',
            'is_ativo' => true,
            'is_controlado' => false,
            'descricao_completa' => 'Descrição completa do novo item',
            'unidade' => 'AM 1.00 ML',
        ];

        $this->patch('/item/edit/1', $postData);
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertEquals('success', $response['status']);
        $this->assertEquals('Item updated successfully.', $response['message']);

        $item = $this->getTableLocator()->get('Item')->get(1);
        $this->assertEquals('Editar Item', $item->nome);
        $this->assertEquals(100, $item->estoque_minimo);
    }

    /**
     * Testa edição de um item que não existe (limite: item inexistente).
     */
    public function testEditNonExistentItem(): void
    {
        $postData = [
            'nome' => 'Qualquer Nome',
            'tipo_item' => 'medicamento_vet'
        ];

        $this->patch('/item/edit/999', $postData);
        $this->assertResponseCode(404); 
    }

    /**
     * Testa envio de método inválido (GET) no edit (limite: método inválido).
     */
    public function testEditInvalidMethod(): void
    {
        $this->get('/item/edit/1');
        $this->assertResponseCode(405);  // Method Not Allowed
    }

    /**
     * Testa envio de dados inválidos (exemplo: nome vazio, limite de validação de campos).
     */
    public function testEditInvalidData(): void
    {
        $postData = [
            'nome' => '',
            'tipo_item' => 'medicamento_vet'
        ];

        $this->patch('/item/edit/1', $postData);
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertEquals('error', $response['status']);
        $this->assertNotEmpty($response['message'], 'Deveria retornar uma mensagem de erro por validação de campos obrigatórios');
    }

    /**
     * Teste do caminho feliz: busca com resultados e paginação normal.
     */
    public function testBuscarItemSuccess(): void
    {
        $this->get('/item/buscarItem?query=insulina&page=1&limit=60');
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('pagination', $response);

        // Valida estrutura da paginação
        $this->assertArrayHasKey('current_page', $response['pagination']);
        $this->assertArrayHasKey('total_pages', $response['pagination']);
        $this->assertArrayHasKey('total_itens', $response['pagination']);
        $this->assertArrayHasKey('limit', $response['pagination']);

        // Deve retornar no máximo o limite
        $this->assertLessThanOrEqual(60, count($response['data']), 'Deve retornar no máximo 60 itens');
    }

    /**
     * Teste de busca com termo que não encontra nenhum item.
     */
    public function testBuscarItemNoResults(): void
    {
        $this->get('/item/buscarItem?query=xxxxxxxx_inexistente&page=1&limit=60');
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertEmpty($response['data'], 'Deveria retornar array vazio quando não há itens');
        $this->assertEquals(0, $response['pagination']['total_itens'], 'Total de itens deveria ser 0');
        $this->assertEquals(0, $response['pagination']['total_pages'], 'Total de páginas deveria ser 0');
    }

    /**
     * Testa quando a página solicitada é maior que o total de páginas.
     */
    public function testBuscarItemPageBeyondTotal(): void
    {
        $totalItems = $this->getTableLocator()->get('Item')->find()->where(['is_ativo' => true])->count();
        $totalPages = (int) ceil($totalItems / 60);

        $this->get('/item/buscarItem?query=&page=' . ($totalPages + 1) . '&limit=60');
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertEmpty($response['data'], 'Quando página excede o total, o resultado deve ser vazio');
    }

    /**
     * Testa busca com limite customizado (exemplo: 5 itens por página).
     */
    public function testBuscarItemCustomLimit(): void
    {
        $this->get('/item/buscarItem?query=&page=1&limit=5');
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertLessThanOrEqual(5, count($response['data']), 'Deve retornar no máximo 5 itens');

        $this->assertEquals(5, $response['pagination']['limit'], 'Limite informado deveria ser refletido na resposta');
    }

    /**
     * Testa com limite inválido (0 ou negativo), deve tratar ou limitar no mínimo 1.
     */
    public function testBuscarItemInvalidLimit(): void
    {
        $this->get('/item/buscarItem?query=&page=1&limit=0');
        $this->assertContentType('text/html');

        $response = json_decode((string)$this->_response->getBody(), true);

        $this->assertEquals(null, $response['data'], 'Mesmo com limite zero, o sistema não pode quebrar');
    }

    /**
     * Testa com página negativa ou zero (sistema deveria tratar como página 1).
     */
    public function testBuscarItemNegativeOrZeroPage(): void
    {
        $this->get('/item/buscarItem?query=&page=0&limit=10');
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $responseZero = json_decode((string)$this->_response->getBody(), true);

        $this->get('/item/buscarItem?query=&page=-5&limit=10');
        $this->assertResponseOk();
        $this->assertContentType('application/json');

        $responseNegative = json_decode((string)$this->_response->getBody(), true);

        $this->get('/item/buscarItem?query=&page=1&limit=10');
        $responsePageOne = json_decode((string)$this->_response->getBody(), true);

        $this->assertEquals($responsePageOne['data'], $responseZero['data'], 'Página 0 deveria se comportar como página 1');
        $this->assertEquals($responsePageOne['data'], $responseNegative['data'], 'Página negativa deveria se comportar como página 1');
    }


}
