<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class MovimentacaoTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('movimentacao');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Setor', [
            'foreignKey' => 'setor_id',
            'joinType' => 'LEFT',
        ]);
        $this->belongsTo('Lote', [
            'foreignKey' => 'id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('LotesMovimentacoes', [
            'foreignKey' => 'movimentacao_id',
            'dependent' => true,
        ]);
        $this->belongsTo('Fornecedor', [
            'foreignKey' => 'fornecedor_id',
            'joinType' => 'LEFT',
        ]);

    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->date('data')
            ->requirePresence('data', 'create', 'o campo data e obrigatorio')
            ->notEmptyDate('data', 'o campo data nao pode estar vazio');

        $validator
            ->scalar('setor_id')
            ->maxLength('setor_id', 255)
            ->allowEmptyString('setor_id');

        $validator
            ->integer('quantidade')
            ->allowEmptyString('quantidade')
            ->greaterThan('quantidade', 0, 'A quantidade deve ser um número positivo');

        $validator
            ->scalar('observacao')
            ->maxLength('observacao', 255)
            ->allowEmptyString('observacao');

        $validator
            ->integer('fornecedor_id')
            ->allowEmptyString('fornecedor_id');

        $validator
            ->scalar('pessoa_requerente')
            ->maxLength('pessoa_requerente', 255)
            ->allowEmptyString('pessoa_requerente');

        $validator
            ->scalar('requerimento')
            ->maxLength('requerimento', 255)
            ->allowEmptyString('requerimento');

        $validator
            ->scalar('tipo_movimentacao')
            ->maxLength('tipo_movimentacao', 20)
            ->inList('tipo_movimentacao', ['entrada', 'saida', 'perda', 'vencimento'], 'Tipo de movimentacao inválida.')
            ->requirePresence('tipo_movimentacao', 'create', 'tipo_movimentacao e obrigatorio')
            ->notEmptyString('tipo_movimentacao', 'o campo tipo_movimentacao nao pode estar vazio');



        $validator
            ->scalar('nome_animal')
            ->maxLength('nome_animal', 255)
            ->allowEmptyString('nome_animal');

        $validator
            ->scalar('ficha_clinica')
            ->maxLength('ficha_clinica', 255)
            ->allowEmptyString('ficha_clinica');

        $validator
            ->scalar('tipo_saida')
            ->maxLength('tipo_saida', 20)
            ->inList('tipo_saida', ['consumo famez','emprestimo'], 'Tipo de saida inválida.')
            ->allowEmptyString('tipo_saida');

        $validator
            ->integer('lote_id')
            ->allowEmptyString('lote_id', null, 'create');

        $validator
            ->scalar('log_lotes_movimentacao')
            ->maxLength('log_lotes_movimentacao', 255)
            ->allowEmptyString('log_lotes_movimentacao');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
{

    $rules->add($rules->existsIn(['setor_id'], 'Setor'), [
        'errorField' => 'setor_id',
        'message' => 'O ID do setor não existe.'
    ]);

    $rules->add($rules->existsIn(['lote_id'], 'Lote'), [
        'errorField' => 'lote_id',
        'message' => 'O ID do lote não existe.'
    ]);

    return $rules;
}
}
