<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CodigoCatmat extends BaseMigration
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

        $table = $this->table('catmat',  ['id' => false]);

        $table->addColumn('codigo_catmat', 'integer', [
            'null' => false,
        ]);

        // Adiciona coluna 'data' com o timestamp atual por padrão
        $table->addColumn('data', 'timestamp', [
            'default' => 'CURRENT_TIMESTAMP',
            'null' => false,
            'update' => 'CURRENT_TIMESTAMP'
        ]);

        // Adiciona a coluna 'item_id' como chave estrangeira
        $table->addColumn('item_id', 'integer', [
            'null' => false,
            'comment' => 'Foreign key to items table'
        ]);

        // Adiciona chave estrangeira
        $table->addForeignKey('item_id', 'item', 'id', [
            'delete' => 'CASCADE',
            'update' => 'NO_ACTION'
        ]);

        $table->addPrimaryKey(['codigo_catmat']);

        $table->create();
    }

    public function down(): void
    {
        $table = $this->table('catmat');

        // Remove as colunas adicionadas e a chave estrangeira
        $table->dropForeignKey('item_id')
              ->removeColumn('codigo_catmat')
              ->removeColumn('data')
              ->removeColumn('item_id')
              ->update();

    }
}
