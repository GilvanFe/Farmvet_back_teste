<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AtualizaChavePrimariaCatmat extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('catmat');

        $table->changePrimaryKey(['codigo_catmat', 'item_id', 'data']);

        $table->update();
    }
}
