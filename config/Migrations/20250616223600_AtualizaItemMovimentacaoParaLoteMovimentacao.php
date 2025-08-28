<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AtualizaItemMovimentacaoParaLoteMovimentacao extends BaseMigration
{
    public function change(): void
    {
        if ($this->hasTable('item_movimentacao')) {
            $this->table('item_movimentacao')->drop()->save();
        }

        $this->table('lote_movimentacao')
            ->addColumn('lote_id', 'integer', ['null' => false])
            ->addColumn('movimentacao_id', 'integer', ['null' => false])
            ->addColumn('quantidade', 'integer', ['null' => false])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => false
            ])
            ->addColumn('modified', 'timestamp', ['null' => true])
            ->addForeignKey('lote_id', 'lote', 'id', [
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
