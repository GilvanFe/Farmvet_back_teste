<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AtualizaSetor extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('setor');
        $table->addColumn('is_ativo', 'boolean', [
            'default' => true,
            'null' => false,
            'after' => 'id'
        ]);

        $table->update();
    }
}
