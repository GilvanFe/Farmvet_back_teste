<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateMovimentacao extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('movimentacao');

        $table->addColumn('data', 'date', [
            'null' => false,
        ])
        ->addPrimaryKey('id')
        ->addColumn('item_id', 'integer', [
            'null' => false,
            'signed' => false,
        ])
        ->addColumn('setor_id', 'string', [
            'null' => true,
        ])
        ->addColumn('quantidade', 'integer', [
            'null' => true,
        ])
        ->addColumn('observacao', 'string', [
            'limit' => 255,
            'null' => true,
        ])
        ->addColumn('pessoa_requerente', 'string', [
            'limit' => 255,
            'null' => true,
        ])
        ->addColumn('requerimento', 'string', [
            'limit' => 255,
            'null' => true,
        ])
        ->addColumn('tipo_movimentacao', 'string', [
            'limit' => 20, // 'entrada', 'saida', perda
            'null' => false,
        ])
        ->addColumn('nome_animal', 'string', [
            'limit' => 255,
            'null' => true,
        ])
        ->addColumn('ficha_clinica', 'string', [
            'limit' => 255,
            'null' => true,
        ])
        ->addColumn('tipo_saida', 'string', [
            'limit' => 20, // 'consumo famez' ou 'emprestimo'
            'null' => true,
        ])
        ->addColumn('lote_id', 'integer', [
            'null' => true,
        ])
        ->addColumn('log_lotes_movimentacao', 'string', [
            'limit' => 255,
            'null' => true,
        ])
        ->addForeignKey('item_id', 'item', 'id', [
            'delete' => 'CASCADE',
            'update' => 'NO_ACTION',
        ])
        ->addForeignKey('setor_id', 'setor', 'codigo', [
            'delete' => 'CASCADE',
            'update' => 'NO_ACTION',
        ])
        ->addForeignKey('lote_id', 'lote', 'id', [
            'delete' => 'CASCADE',
            'update' => 'NO_ACTION',
        ])
        ->create();
    }
}
