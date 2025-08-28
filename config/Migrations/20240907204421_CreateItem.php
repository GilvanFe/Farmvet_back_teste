<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateItem extends BaseMigration
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


        if ($this->hasTable('item')) {
            $this->table('item')->drop()->save();
        }


        $table = $this->table('item', ['id' => false])                                                    //cakephp cria o id automaticamente
                      ->addColumn('id', 'integer',['null'=> false, 'identity' => true])
                      //->addColumn('id', 'integer', ['null' => false])
                      ->addColumn('nome', 'string', ['null' => false])
                      ->addColumn('tipo_item', 'string', [
                        'default' => 'medicamento_vet',
                        'null' => false
                      ])
                      ->addColumn('estoque_minimo', 'integer', ['null' => false])
                      ->addColumn('is_ativo', 'boolean', ['null' => true, 'default' => true])
                      ->addColumn('is_controlado', 'boolean', ['null' => true, 'default' => false])
                      ->addColumn('descricao_completa', 'text', ['null' => true])                       //teste
                      ->addColumn('descricao_complementar', 'string', ['null' => true])
                      ->addColumn('unidade', 'string', ['null' => false])
                      ->addColumn('observacao', 'string',['null' => true])
                      ->addColumn('legislacao_especifica', 'string', ['null' => true])

                      ->addPrimaryKey('id')
                      ->create();
    }

    public function down(): void
    {
        $this->table('item')->drop()->save();
    }
}
