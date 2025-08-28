<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Fornecedor Entity
 *
 * @property int $fornecedor_id
 * @property string $nome
 * @property string|null $telefone
 * @property string $cnpj
 * @property string|null $email
 */
class Fornecedor extends Entity
{
    protected array $_accessible = [
        'nome' => true,
        'telefone' => true,
        'cnpj' => true,
        'email' => true,
    ];
}
