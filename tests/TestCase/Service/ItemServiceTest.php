<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Service\ItemService;
use App\Test\Fixture\CatmatFixture;
use App\Test\Fixture\ItemFixture;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\Fixture\FixtureManager;
use App\Controller\CatmatController;
use Cake\Datasource\Exception\RecordNotFoundException;

class ItemServiceTest extends TestCase
{
    protected ?CatmatController $CatmatController = null;

    protected array $fixtures = [
        ItemFixture::class,
        CatmatFixture::class
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->itemService = new ItemService();
    }

    protected function tearDown(): void
    {
        unset($this->itemService);
        parent::tearDown();
    }

    public function testBuscarPorNomeRetornaResultadosCorretos(): void
    {
        $result = $this->itemService->buscarPorNome('insulina');
        $this->assertCount(1, $result);
        $this->assertContains('insulina', collection($result)->extract('nome')->toList());
    }

    public function testBuscarPorNomeRetornaArrayVazioSeNaoEncontrar(): void
    {
        $result = $this->itemService->buscarPorNome('Produto Inexistente');
        $this->assertCount(0, $result);
    }

    public function testBuscarPorCodigoCatmat(): void
    {
        $result = $this->itemService->buscarPorNome('123456');
        $this->assertNotEmpty($result);
        $this->assertEquals(1, $result[0]->id);
    }

    public function testBuscarPorUnidade(): void
    {
        $result = $this->itemService->buscarPorNome('unidade');
        $this->assertNotEmpty($result);
        $this->assertEquals('unidade', $result[0]->unidade);
    }

    public function testBuscarPorTipoItemMaterial(): void
    {
        $result = $this->itemService->buscarPorNome('material');
        $this->assertNotEmpty($result);
        $this->assertEquals('material', $result[0]->tipo_item);
    }
    
    public function testUpdateItemSuccess(): void
    {
        $data = [
            'nome' => 'Editar Item',
            'tipo_item' => 'medicamento_vet',
            'estoque_minimo' => 100,
            'unidade' => 'AM 1.00 ML',
            'codigo_catmat' => '123456',
            'is_ativo' => true,
            'is_controlado' => false,
            'descricao_completa' => 'Descrição completa do novo item',
        ];

        $result = $this->itemService->updateItemAndMaybeAddCatmat(1, $data);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Item e Catmat atualizados com sucesso.', $result['message']);

        $item = TableRegistry::getTableLocator()->get('Item')->get(1);
        $this->assertEquals('Editar Item', $item->nome);
        $this->assertEquals(100, $item->estoque_minimo);
    }

   public function updateItemAndMaybeAddCatmat($id, $data)
    {
        try {
            $item = $this->itemTable->get($id);
        } catch (RecordNotFoundException $e) {
            return ['status' => 'error', 'message' => 'Item não encontrado.'];
        }
    }

    public function testUpdateItemInvalidData(): void
    {
        $data = [
            'nome' => '',
            'tipo_item' => 'medicamento_vet'
        ];

        $result = $this->itemService->updateItemAndMaybeAddCatmat(1, $data);

        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Erro ao atualizar o item.', $result['message']);
    }

    public function testBuscarItemComResultado(): void
    {
        $result = $this->itemService->buscarPorNome('insulina');
        $this->assertNotEmpty($result);
        $this->assertLessThanOrEqual(60, count($result));
    }

    public function testBuscarItemSemResultados(): void
    {
        $result = $this->itemService->buscarPorNome('xxxxxxxx_inexistente');
        $this->assertEmpty($result);
    }

    public function testSoftDeleteSuccess(): void
    {
        $result = $this->itemService->softDelete(1);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('The item has been soft deleted.', $result['message']);

        $item = TableRegistry::getTableLocator()->get('Item')->get(1);
        $this->assertFalse($item->is_ativo, 'O campo is_ativo deve ser false após soft delete');
    }

    public function testSoftDeleteItemNotFound(): void
    {
        $result = $this->itemService->softDelete(999);

        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Item not found.', $result['message']);
    }

            public function testGetItemsUltimoCatmatMultipleItems(): void
    {
        $results = $this->itemService->getItemsUltimoCatmat();

        $this->assertNotEmpty($results, 'Deveria retornar pelo menos um item');

        foreach ($results as $itemResult) {
            $this->assertArrayHasKey('id', $itemResult);
            $this->assertArrayHasKey('nome', $itemResult);
            $this->assertArrayHasKey('codigo_catmat', $itemResult);

            $catmatTable = TableRegistry::getTableLocator()->get('Catmat');
            $latestCatmat = $catmatTable->find()
                ->where(['item_id' => $itemResult['id']])
                ->order(['data' => 'DESC'])
                ->first();

            $expectedCatmat = $latestCatmat ? $latestCatmat->codigo_catmat : null;
            $this->assertEquals($expectedCatmat, $itemResult['codigo_catmat']);
        }
    }

    public function testGetItemsUltimoCatmatNoItems(): void
    {
        $itemTable = TableRegistry::getTableLocator()->get('Item');
        $itemTable->deleteAll([]);

        $results = $this->itemService->getItemsUltimoCatmat();

        $this->assertEmpty($results, 'Quando não há itens, o resultado deve ser vazio');
    }

    public function testGetItemByIdWithCatmat(): void
    {
        $id = 1;

        $result = $this->itemService->getItemById($id);

        $this->assertEquals('success', $result['status']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($id, $result['data']['id']);

        $catmatTable = TableRegistry::getTableLocator()->get('Catmat');
        $latestCatmat = $catmatTable->find()
            ->where(['item_id' => $id])
            ->order(['data' => 'DESC'])
            ->first();

        $expectedCatmatCode = $latestCatmat ? $latestCatmat->codigo_catmat : null;
        $this->assertEquals($expectedCatmatCode, $result['data']['codigo_catmat']);
    }

    public function testGetItemByIdNotFound(): void
    {
        $nonExistentId = 999;

        $result = $this->itemService->getItemById($nonExistentId);

        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Item não encontrado.', $result['message']);
    }

        public function testCreateItemWithCatmatSuccess(): void
    {
        $data = [
            'nome' => 'Novo Item Teste',
            'tipo_item' => 'medicamento_vet',
            'estoque_minimo' => 50,
            'codigo_catmat' => '987654',
            'unidade' => 'UN',
            'is_ativo' => true,
            'is_controlado' => false,
            'descricao_completa' => 'Descrição completa do novo item',
        ];

        $result = $this->itemService->createItemWithCatmat($data);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Item e catmat criados com sucesso.', $result['message']);

        $item = TableRegistry::getTableLocator()->get('Item')->find()->where(['nome' => 'Novo Item Teste'])->first();
        $this->assertNotNull($item);

        $catmat = TableRegistry::getTableLocator()->get('Catmat')->find()->where(['codigo_catmat' => '987654'])->first();
        $this->assertNotNull($catmat);
        $this->assertEquals($item->id, $catmat->item_id);
    }
    
    public function testCreateItemWithCatmatMissingRequiredField(): void
    {
        $data = [
            'tipo_item' => 'medicamento_vet',
            'estoque_minimo' => 50,
            'codigo_catmat' => '123456',
            'unidade' => 'UN'
        ];

        $result = $this->itemService->createItemWithCatmat($data);

        $this->assertEquals('error', $result['status']);
        $this->assertStringContainsString('Campo obrigatório ausente', $result['message']);
    }

    public function testCreateItemWithCatmatAlreadyExists(): void
    {
        $data = [
            'nome' => 'Item Teste',
            'tipo_item' => 'medicamento_vet',
            'estoque_minimo' => 10,
            'codigo_catmat' => '123456',
            'unidade' => 'UN'
        ];

        $result = $this->itemService->createItemWithCatmat($data);

        $this->assertEquals('error', $result['status']);
        $this->assertEquals('O código catmat já existe.', $result['message']);
    }

    public function testCreateItemWithCatmatValidationError(): void
    {
        $data = [
            'nome' => 'Item Inválido',
            'tipo_item' => 'medicamento_vet',
            'estoque_minimo' => -10,
            'codigo_catmat' => '999999',
            'unidade' => 'UN'
        ];

        $result = $this->itemService->createItemWithCatmat($data);

        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Erro de validação.', $result['message']);
        $this->assertNotEmpty($result['errors']);
    }

}
