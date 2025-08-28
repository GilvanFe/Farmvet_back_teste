<?php
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $builder): void {
    $builder->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);
    $builder->connect('/pages/*', 'Pages::display');
};
