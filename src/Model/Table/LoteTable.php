<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Lote;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Psr\SimpleCache\CacheInterface;

/**
 * Lote Model
 *
 * @property ItemTable&BelongsTo $Item
 *
 * @method Lote newEmptyEntity()
 * @method Lote newEntity(array $data, array $options = [])
 * @method array<Lote> newEntities(array $data, array $options = [])
 * @method Lote get(mixed $primaryKey, array|string $finder = 'all', CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method Lote findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method Lote patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method array<Lote> patchEntities(iterable $entities, array $data, array $options = [])
 * @method Lote|false save(EntityInterface $entity, array $options = [])
 * @method Lote saveOrFail(EntityInterface $entity, array $options = [])
 * @method iterable<Lote>|ResultSetInterface<Lote>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<Lote>|ResultSetInterface<Lote> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<Lote>|ResultSetInterface<Lote>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<Lote>|ResultSetInterface<Lote> deleteManyOrFail(iterable $entities, array $options = [])
 */
class LoteTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('lote');
        $this->setDisplayField('tipo_entrada');
        $this->setPrimaryKey('id');

        $this->belongsTo('Item', [
            'foreignKey' => 'item_id',
        ]);

        $this->hasMany('LotesMovimentacoes', [
            'foreignKey' => 'lote_id',
            'className' => 'LoteMovimentacao',
            'dependent' => true,
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->date('data_vencimento')
            ->requirePresence('data_vencimento', 'create', 'a data de vencimento e obrigatoria')
            ->notEmptyDate('data_vencimento', 'a data de vencimento e obrigatoria');

        $validator
            ->date('data_de_recebimento')
            ->requirePresence('data_de_recebimento', 'create', 'a data de recebimento e obrigatoria')
            ->notEmptyDate('data_de_recebimento', 'a data de recebimento e obrigatoria');

        $validator
            ->integer('quantidade')
            ->requirePresence('quantidade', 'create', 'a quantidade e obrigatoria')
            ->notEmptyString('quantidade', 'a quantidade e obrigatoria');

        $validator
            ->scalar('numero_lote')
            ->requirePresence('numero_lote', 'create', 'numero do lote é obrigatório')
            ->notEmptyString('numero_lote', 'O numero de lote é obrigatório')
            ->maxLength('numero_lote', 255, 'o numero do lote deve ter no maximo 255 caracteres');

        $validator
            ->boolean('is_ativo')
            ->requirePresence('is_ativo', 'create', 'is_ativo é obrigatório');

        $validator
            ->numeric('valor_unitario')
            ->requirePresence('valor_unitario', 'create', 'o valor unitario é obrigatório')
            ->notEmptyString('valor_unitario', 'o valor unitario é obrigatório');

        $validator
            ->numeric('valor_total')
            ->allowEmptyString('valor_total');

        $validator
            ->scalar('via_compra')
            ->maxLength('via_compra', 255)
            ->allowEmptyString('via_compra');

        $validator
            ->scalar('documento_de_origem')
            ->maxLength('documento_de_origem', 255)
            ->allowEmptyString('documento_de_origem');

        $validator
            ->integer('item_id')
            ->allowEmptyString('item_id');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param RulesChecker $rules The rules object to be modified.
     * @return RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['item_id'], 'Item'), ['errorField' => 'item_id']);

        return $rules;
    }
}
