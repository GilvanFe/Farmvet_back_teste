<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * Catmat Entity
 *
 * @property integer $codigo_catmat
 * @property DateTime $data
 * @property int $item_id
 *
 * @property Item $item
 */
class Catmat extends Entity
{
    protected array $_accessible = [
        'codigo_catmat' => true,
        'data' => true,
        'item_id' => true,
        'item' => true,
    ];
}
