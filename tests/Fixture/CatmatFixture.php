<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Cake\I18n\FrozenTime;

class CatmatFixture extends TestFixture
{
    public string $table = 'catmat';

    public function init(): void
    {
        $this->records = [
            [
                'codigo_catmat' => 123456,
                'data' => FrozenTime::now()->format('Y-m-d H:i:s'),
                'item_id' => 1
            ],
            [
                'codigo_catmat' => 654321,
                'data' => FrozenTime::now()->format('Y-m-d H:i:s'),
                'item_id' => 2
            ],
        ];
        parent::init();
    }
}
