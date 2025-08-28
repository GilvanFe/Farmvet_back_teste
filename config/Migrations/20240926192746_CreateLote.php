<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateLote extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('lote');

        $table->addColumn('data_vencimento', 'date', [
            'null' => false,
        ])
            ->addColumn('data_de_recebimento', 'date', [
                'null' => false,
            ])
            ->addColumn('quantidade', 'integer', [
                'null' => false,
            ])
            ->addColumn('valor_unitario', 'float', [
                'null' => false,
            ])
            ->addColumn('valor_total', 'float', [
                'null' => true,
            ])
            ->addColumn('via_compra', 'string', [
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('documento_de_origem', 'string', [
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('fornecedor_id', 'integer', [
                'null' => true,
            ])
            ->addColumn('item_id', 'integer', [
                'null' => true,
            ])
            ->addColumn('tipo_entrada', 'string', [
                'limit' => 20, // Adicionar a validação na controller para: Entrada, empréstimo ou devolução
                'null' => false,
            ])
            ->addColumn('numero_lote', 'biginteger', [
                'null' => false, // Adicionar a validação na controller para: Entrada, empréstimo ou devolução
            ])
            ->addColumn('is_ativo', 'boolean', [
                'null' => false,
            ])
            ->addPrimaryKey('id') // Define 'id' como auto_increment
            ->addForeignKey('fornecedor_id', 'fornecedor', 'fornecedor_id', [
                'delete'=> 'SET_NULL',
                'update'=> 'CASCADE',
            ])
            ->addForeignKey('item_id', 'item', 'id', [
                'delete'=> 'SET_NULL',
                'update'=> 'CASCADE',
            ])
            ->create();
    }
}
