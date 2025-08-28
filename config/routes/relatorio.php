<?php
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $builder): void {
    $builder->connect('/estoque/posicao',
        ['controller' => 'Relatorio', 'action' => 'posicaoAtual'])
        ->setMethods(['GET']);

    $builder->connect('/movimentacao',
        ['controller' => 'Relatorio', 'action' => 'getRelatorioMovimentacao'])
        ->setMethods(['GET']);

    $builder->connect('/consumo',
        ['controller' => 'Relatorio', 'action' => 'estatisticasConsumo'])
        ->setMethods(['GET']);

    $builder->connect('/consumo/export/excel',
        ['controller' => 'Relatorio', 'action' => 'exportarEstatisticasConsumoMensalExcel'])
        ->setMethods(['GET']);
};
