<?php
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $builder): void {
    $builder->connect('/item/add', ['controller' => 'Item', 'action' => 'add']);
    $builder->connect('/item/listagem', ['controller' => 'Item', 'action' => 'index']);
    $builder->connect('/item/softDelete/{id}', ['controller' => 'Item', 'action' => 'softDelete'])
        ->setPass(['id']);
    $builder->connect('/item/search', ['controller' => 'Item', 'action' => 'searchItems']);
    $builder->connect('/item/buscar/search', ['controller' => 'Item', 'action' => 'buscarItem']);
    $builder->connect('/item/view/{id}', ['controller' => 'Item', 'action' => 'view'])
        ->setPass(['id'])
        ->setMethods(['GET']);
    $builder->connect('/item/edit/{id}', ['controller' => 'Item', 'action' => 'edit'])
        ->setPass(['id']);
};
