<?php
declare(strict_types=1);

use Migrations\AbstractSeed;
use Faker\Factory;

class FornecedorSeed extends AbstractSeed
{
    public function run(): void
    {
        $faker = Factory::create('pt_BR');
        $data = [];

        for ($i = 0; $i < 1000; $i++) {
            $data[] = [
                'nome'     => $faker->company,
                'telefone' => $faker->cellphoneNumber, // Sempre gera
                'cnpj'     => preg_replace('/\D/', '', $faker->cnpj),
                'email'    => $faker->companyEmail, // Sempre gera
            ];
        }

        $this->table('fornecedor')
            ->insert($data)
            ->save();
    }
}
