<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class SetorFixture extends TestFixture
{
    public string $table = 'setor';

    public function init(): void
    {
        $this->records = [
            [
                'codigo' => '001',
                'nome' => 'Farmácia'
            ],
            [
                'codigo' => '002',
                'nome' => 'Laboratório'
            ],
            [
                'codigo' => '003',
                'nome' => 'Administrativo'
            ],
            [
                'codigo' => 'TI',
                'nome' => 'Administrativo'
            ],
        ];
        parent::init();
    }
}
