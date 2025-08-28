<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\FornecedorService;
use App\Test\Fixture\FornecedorFixture;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;

class FornecedorServiceTest extends TestCase
{
    protected FornecedorService $service;

    protected array $fixtures = [
        FornecedorFixture::class
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $table = TableRegistry::getTableLocator()->get('Fornecedor');
        $this->service = new FornecedorService($table);
    }

    public function testGetFornecedorByIdSuccess(): void
    {
        $result = $this->service->getFornecedorById(1);

        $this->assertTrue($result['success']);
        $this->assertEquals('Fornecedor A', $result['data']->nome);
    }

    public function testGetFornecedorByIdNotFound(): void
    {
        $result = $this->service->getFornecedorById(9999);

        $this->assertFalse($result['success']);
        $this->assertEquals('Fornecedor não encontrado.', $result['message']);
    }
}
