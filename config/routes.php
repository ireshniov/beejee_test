<?php

use Symfony\Component\Routing;

$routes = new Routing\RouteCollection();

$routes->add('hello', new Routing\Route('/hello/{name}', [
    'name' => 'World',
    '_controller' => 'index_controller:hello'
], [], [], '', [], ['GET']));

$routes->add('task.create', new Routing\Route('/tasks/create', [
    '_controller' => 'task_controller:create'
], [], [], '', [], ['GET', 'POST']));

$routes->add('task.list', new Routing\Route('/tasks', [
    '_controller' => 'task_controller:index'
], [], [], '', [], ['GET']));

return $routes;