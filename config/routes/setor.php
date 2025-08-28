<?php
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $builder): void {
    $builder->connect('/setor/add', ['controller' => 'Setor', 'action' => 'add']);
    $builder->connect('/setor/listagem', ['controller' => 'Setor', 'action' => 'getAll']);
    $builder->connect('/setor/search',['controller' => 'Setor', 'action' => 'search'],['_method' => ['GET']]);
    $builder->connect('/setor/view/{id}', ['controller' => 'Setor', 'action' => 'view'], ['pass' => ['id'], 'id' => '\d+']);
    $builder->connect('/setor/delete/{id}', ['controller' => 'Setor', 'action' => 'delete'], ['pass' => ['id'], 'id' => '\d+']);
    $builder->connect('/setor/edit/{id}', ['controller' => 'Setor', 'action' => 'edit'], ['pass' => ['id'], 'id' => '\d+']);
};
