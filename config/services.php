<?php

use App\Controller\ErrorController;
use App\Controller\IndexController;
use App\Controller\SecurityController;
use App\Controller\TaskController;
use App\EventListener\ContentLengthListener;
use App\EventListener\StringResponseListener;
use Symfony\Component\DependencyInjection\Reference;

$container->register('listener.string_response', StringResponseListener::class)
    ->setArguments(['%charset%'])
;
$container->register('listener.content_length', ContentLengthListener::class)
    ->setArguments(['%charset%'])
;

$container->getDefinition('dispatcher')
    ->addMethodCall('addSubscriber', [new Reference('listener.string_response')])
    ->addMethodCall('addSubscriber', [new Reference('listener.content_length')])
;

$container->register('error_controller', ErrorController::class)
    ->addMethodCall('setContainer', [new Reference('service_container')])
;

$container->register('index_controller', IndexController::class)
    ->addMethodCall('setContainer', [new Reference('service_container')])
;

$container->register('task_controller', TaskController::class)
    ->addMethodCall('setContainer', [new Reference('service_container')])
;

$container->register('security_controller', SecurityController::class)
    ->addMethodCall('setContainer', [new Reference('service_container')])
;
