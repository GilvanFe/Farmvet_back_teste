<?php
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $builder): void {
    $builder->connect('/lote/add', ['controller' => 'Lote', 'action' => 'add']);
    $builder->connect('/lote/listagem', ['controller' => 'Lote', 'action' => 'index']);
    $builder->connect('/lote/item/{itemId}', ['controller' => 'Lote', 'action' => 'porItem'])
        ->setPass(['itemId']);
    $builder->connect('/calcular-debito/{itemId}/{quantidade}',
        ['controller' => 'Lote', 'action' => 'calcularDebito'])
        ->setPass(['itemId', 'quantidade'])
        ->setMethods(['GET'])
        ->setPatterns(['itemId' => '[0-9]+', 'quantidade' => '[0-9]+']);
    $builder->connect('/lote/buscar/:id', ['controller' => 'Lote', 'action' => 'buscar'])
        ->setPass(['id'])
        ->setMethods(['GET']);
    $builder->connect('/lotes/valor-total',
        ['controller' => 'Lote', 'action' => 'getValorTotalDeTodosOsLotes'])
        ->setMethods(['GET']);
    $builder->connect('/verificar-estoque-minimo',
        ['controller' => 'Lote', 'action' => 'verificarEstoqueMinimo'])
        ->setMethods(['GET']);
    $builder->connect('/lote/proximos-vencimento', ['controller' => 'Lote', 'action' => 'lotesProximosVencimento']);
};
