<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Movimentacao Entity
 *
 * @property int $id
 * @property \Cake\I18n\Date $data
 * @property int $item_id
 * @property string $setor_id
 * @property int |null $quantidade
 * @property string|null $observacao
 * @property string|null $pessoa_requerente
 * @property string|null $requerimento
 * @property string $tipo_movimentacao
 * @property string|null $nome_animal
 * @property string|null $ficha_clinica
 * @property string|null $tipo_saida
 *
 * @property Item $item
 * @property Setor $setor
 */
class Movimentacao extends Entity
{
    protected array $_accessible = [
        'tipo_movimentacao' => true,
        'subtipo_movimentacao' => true,
        'data' => true,
        'observacao' => true,
        'fornecedor_id' => true,
        'via_compra' => true,
        'documento_origem' => true,
        'nome_animal' => true,
        'setor_id' => true,
        'requerimento' => true,
        'ficha_clinica' => true,
        'requerente' => true,
        'lotes_movimentacoes' => true
    ];
}
