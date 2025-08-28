<?php
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
        $builder->setExtensions(['json']);

        $builder->get('/swagger.json', [
            'plugin' => 'SwaggerBake',
            'controller' => 'Swagger',
            'action' => 'index'
        ]);

        $builder->get('/docs', [
            'plugin' => 'SwaggerBake',
            'controller' => 'Swagger',
            'action' => 'index'
        ]);

        $builder->connect('/docs/*', [
            'plugin' => 'SwaggerBake',
            'controller' => 'Swagger',
            'action' => 'index'
        ]);
    });

    $routes->scope('/', function (RouteBuilder $builder): void {
        (require __DIR__ . '/routes/fornecedor.php')($builder);
        (require __DIR__ . '/routes/item.php')($builder);
        (require __DIR__ . '/routes/lote.php')($builder);
        (require __DIR__ . '/routes/movimentacao.php')($builder);
        (require __DIR__ . '/routes/pages.php')($builder);
        $builder->scope('/relatorio', function (RouteBuilder $builder): void {
            (require __DIR__ . '/routes/relatorio.php')($builder);
        });
        (require __DIR__ . '/routes/setor.php')($builder);
        $builder->fallbacks(DashedRoute::class);
    });
};
