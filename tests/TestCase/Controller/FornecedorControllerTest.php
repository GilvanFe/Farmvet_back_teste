<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\FornecedorController;
use App\Test\Fixture\FornecedorFixture;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\FornecedorController Test Case
 */
class FornecedorControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        FornecedorFixture::class
    ];

    /**
     * Test add method with valid data
     *
     * @return void
     */
    public function testAddSuccess(): void
{
    $data = [
        'nome' => 'Fornecedor Teste',
        'telefone' => '1234567890',
        'cnpj' => '12345678000195',
        'email' => 'fornecedor@test.com',
    ];

    // Envia a solicitação POST
    $this->post('/fornecedores/add', $data);

    // Verifica se a resposta foi bem-sucedida
    $this->assertResponseSuccess();

    // Obtenha o corpo da resposta e verifique se é um JSON
    $responseBody = (string) $this->_response->getBody();
    $responseJson = json_decode($responseBody, true);

    // Verifique se a resposta JSON contém a mensagem de sucesso
    $this->assertArrayHasKey('success', $responseJson);
    $this->assertTrue($responseJson['success']);
    $this->assertArrayHasKey('message', $responseJson);
    $this->assertEquals('O fornecedor foi salvo com sucesso.', $responseJson['message']);

    // Verifique se o fornecedor foi salvo no banco de dados
    $fornecedores = TableRegistry::getTableLocator()->get('Fornecedor');
    $query = $fornecedores->find()->where(['nome' => 'Fornecedor Teste']);
    $this->assertEquals(1, $query->count());
}



    /**
     * Test add method with missing required fields
     *
     * @return void
     */
    public function testAddMissingFields(): void
    {
        $data = [
            'nome' => '', // Campo obrigatório ausente
            'telefone' => '',
            'cnpj' => '', // Campo obrigatório ausente
            'email' => '',
        ];

        $this->post('/fornecedores/add', $data);

        $this->assertResponseCode(400); // Código de resposta para erro de validação

        $this->assertStringContainsString('O campo nome nao pode ser null', (string) $this->_response->getBody());
        $this->assertStringContainsString('o campo cnpj nao pode ser null', (string) $this->_response->getBody());
    }

    /**
     * Test add method with null fields
     *
     * @return void
     */
    public function testAddNullFields(): void
{
    $data = [
        'nome' => null, // Campo obrigatório nulo
        'telefone' => null,
        'cnpj' => null, // Campo obrigatório nulo
        'email' => null,
    ];

    $this->post('/fornecedores/add', $data);

    $this->assertResponseCode(400); // Código de resposta para erro de validação

    $responseBody = (string) $this->_response->getBody();
    $responseJson = json_decode($responseBody, true);

    // Verificar a mensagem genérica
    $this->assertArrayHasKey('message', $responseJson);
    $this->assertEquals('O fornecedor nao pode ser salvo, tente novamente', $responseJson['message']);

    // Verificar as mensagens de erro específicas
    $this->assertArrayHasKey('errors', $responseJson);
    $errors = $responseJson['errors'];

    // Verificar mensagens específicas para 'nome' e 'cnpj'
    $this->assertArrayHasKey('nome', $errors);
    $this->assertArrayHasKey('_empty', $errors['nome']);
    $this->assertEquals('O campo nome nao pode ser null', $errors['nome']['_empty']);

    $this->assertArrayHasKey('cnpj', $errors);
    $this->assertArrayHasKey('_empty', $errors['cnpj']);
    $this->assertEquals('o campo cnpj nao pode ser null', $errors['cnpj']['_empty']);
}


    /**
     * Test add method with invalid data
     *
     * @return void
     */
    public function testAddInvalidData(): void
    {
        $data = [
            'nome' => 'Fornecedor Teste',
            'telefone' => '1234567890',
            'cnpj' => '12345678', // CNPJ inválido
            'email' => 'invalid-email', // Email inválido
        ];

        $this->post('/fornecedores/add', $data);

        $this->assertResponseCode(400); // Código de resposta para erro de validação
        $this->assertStringContainsString('CNPJ invalido', (string) $this->_response->getBody());
        $this->assertStringContainsString('Email invalido', (string) $this->_response->getBody());
    }

    /**
     * Test add method with missing data
     *
     * @return void
     */
    public function testAddNoData(): void
{
    $data = []; // Nenhum dado enviado

    $this->post('/fornecedores/add', $data);

    $this->assertResponseCode(500); // Código de resposta para erro de validação

    // Verifique a resposta JSON
    $responseBody = json_decode((string) $this->_response->getBody(), true);

    // Mensagem de erro geral
    $expectedMessage = 'O fornecedor nao pode ser salvo, tente novamente';
    $this->assertArrayHasKey('message', $responseBody);
    $this->assertEquals($expectedMessage, $responseBody['message']);

    // Verifique os erros específicos dos campos
    $this->assertArrayHasKey('errors', $responseBody);
    $errors = $responseBody['errors'];

    // Mensagens específicas dos campos
    $this->assertArrayHasKey('nome', $errors);
    $this->assertArrayHasKey('_required', $errors['nome']);
    $this->assertEquals('O campo nome é obrigatorio', $errors['nome']['_required']);

    $this->assertArrayHasKey('cnpj', $errors);
    $this->assertArrayHasKey('_required', $errors['cnpj']);
    $this->assertEquals('o campo cnpj é obrigatorio', $errors['cnpj']['_required']);
}


    /**
     * Test add method with invalid request method
     *
     * @return void
     */
    public function testAddInvalidRequestMethod(): void
    {
        $this->get('/fornecedores/add'); // Usando GET em vez de POST

        $this->assertResponseCode(500); // Código de resposta para método não permitido
        $this->assertStringContainsString('metodo de requisicao invalido', (string) $this->_response->getBody());
    }
}
