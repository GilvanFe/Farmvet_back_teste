<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Item Entity
 *
 * @property int $id
 * @property string $nome
 * @property string $tipo_item
 * @property int $estoque_minimo
 * @property bool $is_ativo
 * @property bool $is_controlado
 * @property string $descricao_completa
 * @property string $descricao_complementar
 * @property string $unidade
 * @property string $observacao
 * @property string $legislacao_especifica
 */
class Item extends Entity
{
    protected array $_accessible = [
        'nome' => true,
        'tipo_item' => true,
        'estoque_minimo' => true,
        'is_ativo' => true,
        'is_controlado' => true,
        'descricao_completa' => true,
        'descricao_complementar' => true,
        'unidade' => true,
        'observacao' => true,
        'legislacao_especifica' => true,
    ];
}
