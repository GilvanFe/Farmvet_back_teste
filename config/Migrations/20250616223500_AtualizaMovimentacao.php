<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AtualizaMovimentacao extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('movimentacao');

        if ($table->hasColumn('quantidade')) {
            $table->removeColumn('quantidade');
        }

        if ($table->hasColumn('tipo_saida')) {
            $table->removeColumn('tipo_saida');
        }

        if ($table->hasColumn('item_id')) {
            $table->dropForeignKey('item_id')
                ->removeColumn('item_id');
        }

        if ($table->hasColumn('lote_id')) {
            $table->dropForeignKey('lote_id')
                ->removeColumn('lote_id');
        }

        $table
            ->addColumn('subtipo_movimentacao', 'string', [
                'limit' => 20,
                'null' => true,
                'after' => 'tipo_movimentacao'
            ])
            ->addColumn('fornecedor_id', 'integer', [
                'null' => true,
                'after' => 'subtipo_movimentacao'
            ])
            ->addColumn('via_compra', 'string', [
                'limit' => 255,
                'null' => true,
                'after' => 'log_lotes_movimentacao'
            ])
            ->addColumn('documento_origem', 'string', [
                'limit' => 255,
                'null' => true,
                'after' => 'via_compra'
            ]);

        $table
            ->addForeignKey('fornecedor_id', 'fornecedor', 'fornecedor_id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
            ]);

        $table->update();
    }
}
