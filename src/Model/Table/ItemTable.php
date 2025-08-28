<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class ItemTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('item');
        $this->setDisplayField('nome');
        $this->setPrimaryKey('id');

        $this->hasMany('Catmat', [
            'foreignKey' => 'item_id',
        ]);

        $this->hasMany('Lote', [
            'foreignKey' => 'item_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('nome')
            ->maxLength('nome', 255, 'O nome nao pode ter mais de 255 caracteres.')
            ->requirePresence('nome', 'create', 'O campo nome e obrigatorio')
            ->notEmptyString('nome', 'O campo nome nao pode estar vazio');

        $validator
            ->scalar('tipo_item')
            ->maxLength('tipo_item', 255, 'Quantidade maxima de caracteres é 255')
            ->inList('tipo_item', ['material', 'farmacologico', 'medicamento_vet'], 'Tipo de item inválido.')
            ->notEmptyString('tipo_item', 'O campo tipo_item nao pode estar vazio');

        $validator
            ->integer('estoque_minimo')
            ->requirePresence('estoque_minimo', 'create', 'O campo estoque minimo é obrigatorio')
            ->notEmptyString('estoque_minimo', 'estoque minimo nao pode ser null');

        $validator
            ->boolean('is_ativo')
            ->requirePresence('is_ativo', 'create', 'O campo ativo é obrigatorio');


        $validator
            ->boolean('is_controlado')
            ->requirePresence('is_controlado', 'create', 'O campo is_controlado é obrigatorio');


        $validator
            ->scalar('descricao_completa')
            ->requirePresence('descricao_completa', 'create', 'O campo descrição é obrigatório.')
            ->notEmptyString('descricao_completa', 'O campo descricao_completa nao pode estar vazio');

        $validator
            ->scalar('descricao_complementar')
            ->maxLength('descricao_complementar', 255, 'Quantidade maxima de caracteres atingida')
            ->allowEmptyString('descricao_complementar');


        $validator
        ->scalar('unidade')
        ->maxLength('unidade', 255, 'A quantidade máxima de caracteres é 255')
        ->inList('unidade', [
            'AM 1.00 ML', 'AM 10.00 ML', 'AM 2.00 ML', 'AM 2.50 ML', 'AM 20.00 ML', 'AM 3.00 ML', 'AM 4.00 ML', 'AM 5.00 ML',
            'BIS 15.00 G', 'BOL 100.00 ML', 'BOL 250.00 ML', 'BOL 500.00 ML', 'CX 100.00 UN', 'CX 3.00 UN', 'CX 50',
            'EMB 100.00 UN', 'EMB 4.50 KG', 'EMB 500.00 G', 'ENV', 'FR 1.00 L', 'FR 1.00 ML', 'FR 10.00 ML', 'FR 100.00 ML',
            'FR 1000.00 ML', 'FR 15.00 ML', 'FR 150.00 ML', 'FR 2.00 ML', 'FR 20.00 ML', 'FR 200.00 G', 'FR 250.00 ML',
            'FR 3.00 ML', 'FR 30.00 ML', 'FR 5.00 ML', 'FR 50.00 ML', 'FR 500.00 ML', 'FR-AM', 'G', 'GL 5.00 L', 'FR 300G',
            'M', 'PAR', 'PCT 100.00 UN', 'PCT 50.00 UN', 'PCT 500.00 UN', 'POTE 350.00 G', 'POTE 700.00 G', 'RO 1.00 UN',
            'RO 1.80 M', 'RO 10.00 M', 'RO 100.00 M', 'RO 15.00 M', 'RO 25.00 M', 'RO 4.50 M', 'RO 91.00 M', 'SAC 8.00 G',
            'SER 30.00 G', 'TAB', 'UN'
        ], 'Unidade inválida.')
        ->notEmptyString('unidade', 'O campo unidade não pode estar vazio');

        $validator
            ->scalar('observacao')
            ->maxLength('observacao', 255, 'Quantidade maxima de caracteres atingida')
            ->allowEmptyString('observacao')
            ->allowEmptyString('observacao');

        $validator
            ->scalar('legislacao_especifica')
            ->maxLength('legislacao_especifica', 255, ' Quantidade maxima de caracteres atingida')
            ->allowEmptyString('legislacao_especifica');

        return $validator;
    }
}
