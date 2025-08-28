<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Setor Entity
 *
 * @property string $codigo
 * @property string $nome
 */
class Setor extends Entity
{
    protected array $_accessible = [
        'nome' => true,
        'codigo' => true
    ];
}
