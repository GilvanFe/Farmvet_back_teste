<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\SetorService;
use App\Test\Fixture\SetorFixture;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;
use Cake\ORM\Entity;
use App\Model\Table\SetorTable;
use PHPUnit\Framework\MockObject\MockObject;
use Cake\Log\Log;

class SetorServiceTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        SetorFixture::class
    ];

    /** @var SetorService */
    private $setorService;

    /** @var MockObject */
    private $setorTableMock;

    public function setUp(): void
    {

        parent::setUp();

        $this->setorTableMock = $this->createMock(SetorTable::class);


        $this->setorService = new SetorService($this->setorTableMock);

    }

    public function testCreateSetorSuccess(): void
    {
        $data = [
            'codigo' => '0010',
            'nome' => 'Farmácia'
        ];

        $setorEntity = new Entity($data);

        $this->setorTableMock->method('newEntity')->willReturn($setorEntity);
        $this->setorTableMock->method('save')->willReturn($setorEntity);

        $result = $this->setorService->createSetor($data);

        $this->assertTrue($result['success']);
        $this->assertEquals('O setor foi salvo com sucesso.', $result['message']);
        $this->assertEquals($setorEntity['nome'], $result['data']['nome']);
    }



    public function testCreateSetorValidationError(): void
    {
        $data = [
            'nome' => '',
            'codigo' => '4543634'
        ];

        $setorEntity = new Entity($data);
        $setorEntity->setErrors(['nome' => ['_empty' => 'campo nome nao pode estar vazio']]);

        $this->setorTableMock->method('newEntity')->willReturn($setorEntity);
        $this->setorTableMock->method('save')->willReturn(false);

        $result = $this->setorService->createSetor($data);

        $this->assertFalse($result['success']);
        $this->assertEquals('O setor não pode ser salvo, tente novamente.', $result['message']);
        $this->assertEquals(['nome' => ['_empty' => 'campo nome nao pode estar vazio']], $result['errors']);
    }

    public function testCreateSetorNullError(): void
{
    $data = [
        'nome' => '',
        'codigo' => ''
    ];

    $setorEntity = new Entity($data);

    $setorEntity->setErrors([
        'nome' => ['_empty' => 'campo nome nao pode estar vazio'],
        'codigo' => ['_empty' => 'campo codigo nao pode estar vazio']
    ]);

    $this->setorTableMock->method('newEntity')->willReturn($setorEntity);
    $this->setorTableMock->method('save')->willReturn(false);

    $result = $this->setorService->createSetor($data);

    $this->assertFalse($result['success']);
    $this->assertEquals('O setor não pode ser salvo, tente novamente.', $result['message']);

    $this->assertEquals([
        'nome' => ['_empty' => 'campo nome nao pode estar vazio'],
        'codigo' => ['_empty' => 'campo codigo nao pode estar vazio']
    ], $result['errors']);
}

public function testCreateSetorMissFieldError(): void
{
    $data = [
    ];

    $setorEntity = new Entity($data);
    $setorEntity->setErrors([
        'nome' => ['_required' => 'This field is required'],
        'codigo' => ['_required' => 'This field is required']
    ]);

    $this->setorTableMock->method('newEntity')->willReturn($setorEntity);
    $this->setorTableMock->method('save')->willReturn(false);

    $result = $this->setorService->createSetor($data);

    $this->assertFalse($result['success']);
    $this->assertEquals('O setor não pode ser salvo, tente novamente.', $result['message']);

    $this->assertEquals([
        'nome' => ['_required' => 'This field is required'],
        'codigo' => ['_required' => 'This field is required']
    ], $result['errors']);
}


/**
 * Testa o caso onde não há setores disponíveis
 */
public function testGetAllSetoresWithoutData(): void
{
    $setorTable = TableRegistry::getTableLocator()->get('Setor');
    $setorTable->deleteAll([]); // Deleta todos os registros

    $setores = $this->setorService->getAllSetores();

    $this->assertFalse($setores['success']);
    $this->assertEquals('Nenhum setor encontrado.', $setores['message']);
}




}
