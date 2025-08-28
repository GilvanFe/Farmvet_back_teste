<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AtualizaLote extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('lote');

        if ($table->hasColumn('via_compra')) {
            $table->removeColumn('via_compra');
        }

        if ($table->hasColumn('documento_de_origem')) {
            $table->removeColumn('documento_de_origem');
        }

        if ($table->hasColumn('fornecedor_id')) {
            $table->dropForeignKey('fornecedor_id')
                ->removeColumn('fornecedor_id');
        }

        if ($table->hasColumn('tipo_entrada')) {
            $table->removeColumn('tipo_entrada');
        }

        if ($table->hasColumn('numero_lote')) {
            $this->execute('ALTER TABLE lote ALTER COLUMN numero_lote TYPE VARCHAR(255) USING numero_lote::text;');
        }

        $table->update();
    }
}
