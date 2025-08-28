<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

class LotesMovimentacoesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('lote_movimentacao');
        $this->setPrimaryKey('id');

        $this->belongsTo('Lote', [
            'foreignKey' => 'lote_id',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('Movimentacao', [
            'foreignKey' => 'movimentacao_id',
            'joinType' => 'INNER',
        ]);
    }
}
