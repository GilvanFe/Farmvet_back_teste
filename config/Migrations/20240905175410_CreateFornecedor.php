<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateFornecedor extends BaseMigration
{
    public bool $autoId = false;

    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('fornecedor');
        $table->addColumn('fornecedor_id', 'integer', [
            'autoIncrement' => true,
            'default' => null,
            'limit' => 11,
            'null' => false,
        ]);
        $table->addColumn('nome', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('telefone', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,

        ]);
        $table->addColumn('cnpj', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        $table->addColumn('email', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
        ]);


        $table->addIndex(['email'], [
            'name' => 'UNIQUE_EMAIL',
            'unique' => true,
        ]);

        $table->addIndex(['cnpj'], [
            'name' => 'UNIQUE_CNPJ',
            'unique' => true,
        ]);

        $table->addIndex(['telefone'], [
            'name' => 'UNIQUE_TELEFONE',
            'unique' => true,
        ]);

        $table->addIndex([
            'nome',
            ], [
            'name' => 'BY_NOME',
            'unique' => false,
        ]);
        $table->addPrimaryKey([
            'fornecedor_id',
        ]);
        $table->create();
    }
}
