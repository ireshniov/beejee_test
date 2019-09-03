<?php

use Symfony\Component\DependencyInjection;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher;
use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpKernel;
use Symfony\Component\Routing;
use Symfony\Component\Security\Http\HttpUtils;

$containerBuilder = new DependencyInjection\ContainerBuilder();

require_once 'doctrine_orm.php';
require_once 'form.php';
require_once 'twig.php';
require_once 'translator.php';
require_once 'validator.php';
require_once 'security.php';
require_once 'logger.php';

$routes = include __DIR__ . '/../routes.php';

$containerBuilder->register('context', Routing\RequestContext::class);

//TODO implement cacheable routing (using router instead urlMatcher;
// https://stackoverflow.com/questions/31225578/how-to-cache-routes-when-using-symfony-routing-as-a-standalone/31229815
$containerBuilder->register('matcher', Routing\Matcher\UrlMatcher::class)
    ->setArguments([$routes, new Reference('context')])
;

$containerBuilder->register('url_generator', Routing\Generator\UrlGenerator::class)
    ->setArguments([
        $routes,
        new Reference('context'),
        new Reference('logger'),
        '%locale%'
    ])
;

$containerBuilder->register('http_utils', HttpUtils::class)
    ->setArguments([new Reference('url_generator'), new Reference('matcher')])
;

$containerBuilder->register('request_stack', HttpFoundation\RequestStack::class);
$containerBuilder->register('controller_resolver', HttpKernel\Controller\ContainerControllerResolver::class)
    ->setArguments([
        new Reference('service_container'),
        new Reference('logger')
    ])
;
$containerBuilder->register('argument_resolver', HttpKernel\Controller\ArgumentResolver::class);

$containerBuilder->register('listener.router', HttpKernel\EventListener\RouterListener::class)
    ->setArguments([
        new Reference('matcher'),
        new Reference('request_stack'),
        new Reference('context'),
        new Reference('logger')
    ])
;
$containerBuilder->register('listener.response', HttpKernel\EventListener\ResponseListener::class)
    ->setArguments(['%charset%'])
;
$containerBuilder->register('listener.exception', HttpKernel\EventListener\ExceptionListener::class)
    ->setArguments([
        'error_controller:exception',
        new Reference('logger')
    ])
;

$containerBuilder->register('session', HttpFoundation\Session\Session::class)
//    ->setArguments([new HttpFoundation\Session\Storage\PhpBridgeSessionStorage()])
;

$containerBuilder->register('listener.session', HttpKernel\EventListener\SessionListener::class)
    ->addArgument(new Reference('service_container'))
;

$containerBuilder->register('dispatcher', EventDispatcher\EventDispatcher::class)
    ->addMethodCall('addSubscriber', [new Reference('listener.router')])
    ->addMethodCall('addSubscriber', [new Reference('listener.response')])
    ->addMethodCall('addSubscriber', [new Reference('listener.exception')])
    ->addMethodCall('addSubscriber', [new Reference('listener.session')])
    ->addMethodCall('addSubscriber', [new Reference('firewall')])
;

$containerBuilder->register('framework', HttpKernel\HttpKernel::class)
    ->setArguments([
        new Reference('dispatcher'),
        new Reference('controller_resolver'),
        new Reference('request_stack'),
        new Reference('argument_resolver'),
    ])
;

$containerBuilder->register('cache.store', HttpKernel\HttpCache\Store::class)
    ->setArguments(['%cache.dir%'])
;

$containerBuilder->register('framework.cache', HttpKernel\HttpCache\HttpCache::class)
    ->setArguments([
        new Reference('framework'),
        new Reference('cache.store'),
    ])
;

return $containerBuilder;