<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class FornecedorFixture extends TestFixture
{
    public string $table = 'fornecedor';

    public function init(): void
    {
        $this->records = [
            [
                'nome' => 'Fornecedor A',
                'telefone' => '67999990000',
                'cnpj' => '12.345.678/0001-90',
                'email' => 'fornecedorA@exemplo.com'
            ],
            [
                'nome' => 'Fornecedor B',
                'telefone' => '67988887777',
                'cnpj' => '98.765.432/0001-10',
                'email' => 'fornecedorB@exemplo.com'
            ],
        ];
        parent::init();
    }
}
