<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class CatmatTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('catmat');
        $this->setDisplayField('codigo_catmat');
        
        // Define chave primária composta (todos os campos como PK)
        $this->setPrimaryKey(['codigo_catmat', 'item_id']); 

        $this->belongsTo('Item', [
            'foreignKey' => 'item_id',
            'joinType' => 'INNER',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->dateTime('data')
            ->notEmptyDateTime('data');

        $validator
            ->integer('item_id')
            ->requirePresence('item_id', 'create')
            ->notEmptyString('item_id');

        $validator
            ->scalar('codigo_catmat')
            ->requirePresence('codigo_catmat', 'create')
            ->notEmptyString('codigo_catmat')
            ->lengthBetween('codigo_catmat', [1, 7], 'O código CATMAT deve ter entre 1 e 7 dígitos.')
            ->regex('codigo_catmat', '/^\d+$/', 'O código CATMAT deve conter apenas números.');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        // Verifica se o item_id existe na tabela Item
        $rules->add($rules->existsIn(['item_id'], 'Item'), [
            'errorField' => 'item_id',
            'message' => 'O Item associado não existe.'
        ]);

        // Garante que a combinação dos 3 campos seja única
        $rules->add($rules->isUnique(
            ['codigo_catmat', 'data', 'item_id'], 
            'Já existe um registro com esta combinação de código, data e item.'
        ));

        return $rules;
    }
}