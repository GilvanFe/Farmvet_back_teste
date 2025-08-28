<?php
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $builder): void {
    $builder->connect('/movimentacao/add', ['controller' => 'Movimentacao', 'action' => 'add']);
    $builder->connect('/movimentacao/listagem', ['controller' => 'Movimentacao', 'action' => 'index']);
    $builder->connect('/movimentacoes/saida/search', ['controller' => 'Movimentacao', 'action' => 'searchMovimentacaoSaida']);
    $builder->connect('/movimentacoes/vencimento/search', ['controller' => 'Movimentacao', 'action' => 'searchMovimentacaoVencimento']);
    $builder->connect('/movimentacoes/perda/search', ['controller' => 'Movimentacao', 'action' => 'searchMovimentacaoPerda']);
    $builder->connect('/movimentacoes/perda', ['controller' => 'Movimentacao', 'action' => 'addPerda', '_method' => 'POST']);
    $builder->connect('/movimentacoes/entrada/search', ['controller' => 'Movimentacao', 'action' => 'searchMovimentacaoEntrada']);
    $builder->connect('/movimentacao/lote/{id}', ['controller' => 'Movimentacao', 'action' => 'listarPorLoteId'])->setPass(['id']);

};