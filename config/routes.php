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

$routes->add('task.update', new Routing\Route('/tasks/{id}/update', [
    '_controller' => 'task_controller:update'
], ['id' => '\d+'], [], '', [], ['GET', 'POST']));

$routes->add('task.complete', new Routing\Route('/tasks/{id}/complete', [
    '_controller' => 'task_controller:complete'
], ['id' => '\d+'], [], '', [], ['GET']));

$routes->add('task.list', new Routing\Route('/tasks', [
    '_controller' => 'task_controller:index'
], [], [], '', [], ['GET']));

$routes->add('app_login', new Routing\Route('/login', [
    '_controller' => 'security_controller:login'
], [], [], '', [], ['GET', 'POST']));

return $routes;