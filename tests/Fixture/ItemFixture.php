<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ItemFixture extends TestFixture
{
    public string $table = 'item';

    public function init(): void
    {
        $this->records = [
            [
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
            ],
            [
                'nome' => 'gaze',
                'tipo_item' => 'material',
                'estoque_minimo' => 50,
                'is_ativo' => true,
                'is_controlado' => false,
                'descricao_completa' => null,
                'descricao_complementar' => null,
                'unidade' => 'unidade',
                'observacao' => null,
                'legislacao_especifica' => null,
            ],
        ];
        parent::init();
    }
}
