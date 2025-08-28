<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Setor Model
 *
 * @method \App\Model\Entity\Setor newEmptyEntity()
 * @method \App\Model\Entity\Setor newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Setor> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Setor get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Setor findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Setor patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Setor> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Setor|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Setor saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Setor>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Setor>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Setor>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Setor> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Setor>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Setor>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Setor>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Setor> deleteManyOrFail(iterable $entities, array $options = [])
 */
class SetorTable extends Table
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

        $this->setTable('setor');
        $this->setDisplayField('codigo');
        $this->setPrimaryKey('codigo');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
{
    $validator
        ->scalar('nome')
        ->maxLength('nome', 255, 'quantidade maxima de caracteres atingida')
        ->requirePresence('nome', 'create')
        ->notEmptyString('nome', 'campo nome nao pode estar vazio');

    $validator
        ->scalar('codigo')
        ->maxLength('codigo', 255, 'quantidade maxima de caracteres atingida')
        ->requirePresence('codigo', 'create')
        ->notEmptyString('codigo', 'campo codigo nao pode estar vazio')
        ->add('codigo', 'unique', [
            'rule' => 'validateUnique',
            'provider' => 'table',
            'message' => 'O código deve ser único.'
        ]);

    return $validator;
}
}
