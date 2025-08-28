<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\SetorController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use App\Test\Fixture\SetorFixture;

class SetorControllerTest extends TestCase
{
    use IntegrationTestTrait;

    public array $fixtures = [
        SetorFixture::class,
    ];

    public function up(): void
    {
        $table = $this->table('setor');
        $table->addColumn('is_ativo', 'boolean', [
            'default' => true,
            'null' => false,
            'after' => 'id'
        ]);
        $table->update();
    }

    public function down(): void
    {
        $table = $this->table('setor');
        if ($table->hasColumn('is_ativo')) {
            $table->removeColumn('is_ativo');
            $table->update();
        }
    }
    public function testCreateSetorSuccess(): void
    {
        $data = [
            'nome' => 'RH',
            'codigo' => '54365346'
        ];

        $this->post('/setor/add', $data);

        $this->assertResponseSuccess();

        $setor = $this->getTableLocator()->get('Setor')->find()->where(['nome' => 'RH'])->first();
        $this->assertNotEmpty($setor);
        $this->assertEquals('RH', $setor->nome);
    }

    public function testCreateSetorValidationError(): void
    {
        $data = [
            'nome' => '',
            'codigo' => '3333'
        ];

        $this->post('/setor/add', $data);

        $this->assertResponseCode(400);
        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertFalse($response['success']);
        $this->assertEquals('O setor não pode ser salvo, tente novamente.', $response['message']);
        $this->
        assertArrayHasKey('nome', $response['errors']);
    }

    public function testAddSetorCodigoNullError(): void
    {
        $data = [
            'nome' => 'dasdas',
            'codigo' => ''
        ];
        $this->post('/setor/add', $data);
        $this->assertResponseCode(400);
        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertFalse($response['success']);
        $this->assertEquals('O setor não pode ser salvo, tente novamente.', $response['message']);
        $this->assertArrayHasKey('codigo', $response['errors']);
    }

    public function testEditSuccess(): void
    {
        $data = [
            'nome' => 'Setor Editado',
            'codigo'=> 'TI'
        ];
        $this->patch('/setor/edit/TI', $data);
        $response = json_decode((string)$this->_response->getBody(), true);
        
        $this->assertResponseOk();
        $this->assertTrue($response['success']);
        $this->assertEquals('Setor Editado', $response['data']['nome']);
    }

    public function testDeleteSuccess(): void
    {
        $this->post('/setor/delete/002');
        $this->assertResponseOk();
        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertTrue($response['success']);
    }

    public function testSearchSuccess(): void
    {
        $this->get('/setor/search?query=adm&page=1&limit=10');
        $this->assertResponseOk();
        $response = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('pagination', $response);
        $this->assertIsArray($response['data']);
    }

}
