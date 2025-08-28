<?php
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $builder): void {
    $builder->connect('/fornecedores/add', ['controller' => 'Fornecedor', 'action' => 'add']);
    $builder->connect('/fornecedores/listagem', ['controller' => 'Fornecedor', 'action' => 'getAll']);
    $builder->connect('/fornecedores/nome/search', ['controller' => 'Fornecedor', 'action' => 'searchFornecedorNome']);
    $builder->connect('/fornecedores/{id}', ['controller' => 'Fornecedor', 'action' => 'getFornecedorById'])->setPass(['id']);
    $builder->connect(
        '/fornecedores/edit/{id}',
        ['controller' => 'Fornecedor', 'action' => 'edit']
    )
    ->setPass(['id'])
    ->setMethods(['PATCH', 'PUT', 'POST']);
    
    $builder->connect(
        '/fornecedores/delete/{id}',
        ['controller' => 'Fornecedor', 'action' => 'delete']
    )
    ->setPass(['id'])
    ->setMethods(['DELETE', 'POST']);
};
