<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Fornecedor Model
 *
 * @method \App\Model\Entity\Fornecedor newEmptyEntity()
 * @method \App\Model\Entity\Fornecedor newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Fornecedor> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Fornecedor get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Fornecedor findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Fornecedor patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Fornecedor> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Fornecedor|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Fornecedor saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Fornecedor>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Fornecedor>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Fornecedor>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Fornecedor> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Fornecedor>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Fornecedor>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Fornecedor>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Fornecedor> deleteManyOrFail(iterable $entities, array $options = [])
 */
class FornecedorTable extends Table
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

        $this->setTable('fornecedor');
        $this->setDisplayField('nome');
        $this->setPrimaryKey('fornecedor_id');
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
            ->maxLength('nome', 255, 'tamanho não pode exceder a 255 caracteres')
            ->requirePresence('nome', 'create', "O campo nome é obrigatorio")
            ->notEmptyString('nome', "O campo nome nao pode ser null");

        $validator
            ->scalar('telefone')
            ->maxLength('telefone', 255)
            ->allowEmptyString('telefone');

        $validator
            ->scalar('cnpj')
            ->maxLength('cnpj', 255)
            ->requirePresence('cnpj', 'create', 'o campo cnpj é obrigatorio')
            ->notEmptyString('cnpj', 'o campo cnpj nao pode ser null')
            ->add('cnpj', 'validFormat', [
                'rule' => ['custom', '/^\d{14}$/'], // Exemplo de regra de CNPJ com 14 dígitos
                'message' => 'CNPJ invalido'
            ]);

        $validator
            ->email('email', false, 'Email invalido')
            ->allowEmptyString('email');

        return $validator;
    }

    /**
     * Build rules for validation.
     *
     * @param \Cake\ORM\RulesChecker $rules RulesChecker instance.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add(
            $rules->isUnique(
                ['email'],
                'Esse e-mail já está em uso.'
            ),
            'uniqueEmail'
        );

        $rules->add(
            $rules->isUnique(
                ['cnpj'],
                'Esse cnpj já está cadastrado.'
            ),
            'uniqueCnpj'
        );

        $rules->add(
            $rules->isUnique(
                ['telefone'],
                'Esse telefone já está em uso.'
            ),
            'uniqueTelefone'
        );

        return $rules;
    }
}
