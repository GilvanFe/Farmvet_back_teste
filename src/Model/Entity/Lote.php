<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\Date;
use Cake\ORM\Entity;

/**
 * Lote Entity
 *
 * @property int $id
 * @property Date $data_vencimento
 * @property Date $data_de_recebimento
 * @property int $quantidade
 * @property float $valor_unitario
 * @property float|null $valor_total
 * @property string|null $via_compra
 * @property string|null $documento_de_origem
 * @property int|null $fornecedor_id
 * @property int|null $item_id
 * @property string $tipo_entrada
 *
 * @property Fornecedor $fornecedor
 * @property Item $item
 */
class Lote extends Entity
{
    protected array $_accessible = [
        'item_id' => true,
        'numero_lote' => true,
        'data_vencimento' => true,
        'data_de_recebimento' => true,
        'quantidade' => true,
        'is_ativo' => true,
        'valor_unitario' => true,
        'valor_total' => true


    ];
}
