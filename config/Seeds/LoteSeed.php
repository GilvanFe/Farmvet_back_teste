<?php
declare(strict_types=1);

use Migrations\AbstractSeed;
use Cake\ORM\TableRegistry;
use Faker\Factory;

class LoteSeed extends AbstractSeed
{
    public function run(): void
    {
        $connection = $this->getAdapter()->getConnection();
        $itemTable = TableRegistry::getTableLocator()
            ->get('Item', ['connection' => $connection]);

        $allItemIds = (array)$itemTable
            ->find('list', ['keyField' => 'id', 'valueField' => 'id'])
            ->toArray();

        if (empty($allItemIds)) {
            $this->out('ERRO: nenhum item encontrado. Rode ItemSeed primeiro.', 1);
            return;
        }

        $validItemIds = array_slice($allItemIds, 0, 200, true);
        $faker = Factory::create('pt_BR');
        $data  = [];

        for ($i = 0; $i < 200; $i++) {
            $quantidade    = $faker->numberBetween(1, 200);
            $valorUnitario = $faker->randomFloat(2, 1, 150);

            $data[] = [
                'data_vencimento'     => $faker->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
                'data_de_recebimento' => $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
                'quantidade'          => $quantidade,
                'valor_unitario'      => $valorUnitario,
                'valor_total'         => $quantidade * $valorUnitario,
                'item_id'             => $faker->randomElement($validItemIds),
                'numero_lote'         => strtoupper($faker->bothify('L###-'.date('Ym').'-??')),
                'is_ativo'            => true,
            ];
        }

        $this->table('lote', ['noTimestamps' => true])
            ->insert($data)
            ->save();
    }
}
