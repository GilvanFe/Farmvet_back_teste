<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateItemMovimentacao extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('item_movimentacao');

        $table->addColumn('item_id', 'integer', [
            'null' => false,
        ])
        ->addColumn('movimentacao_id', 'integer', [
            'null' => false,
        ])
        ->addForeignKey('item_id', 'item', 'id', [
            'delete'=> 'CASCADE',
            'update'=> 'NO_ACTION'
        ])
        ->addForeignKey('movimentacao_id', 'movimentacao', 'id', [
            'delete'=> 'CASCADE',
            'update'=> 'NO_ACTION'
        ])
        ->create();
    }
}
