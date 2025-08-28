<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * LotesMovimentacoes Entity
 *
 * @property int $id
 * @property int $lote_id
 * @property int $movimentacao_id
 * @property int|null $quantidade
 *
 * @property Lote $lote
 * @property Movimentacao $movimentacao
 */
class LotesMovimentacoes extends Entity
{
    protected array $_accessible = [
        'lote_id' => true,
        'movimentacao_id' => true,
        'quantidade' => true,
        'lote' => true,
        'movimentacao' => true,
    ];
}
