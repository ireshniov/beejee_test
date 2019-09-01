<?php

use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\DependencyInjection;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher;
use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpKernel;
use Symfony\Component\Routing;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$containerBuilder = new DependencyInjection\ContainerBuilder();

$containerBuilder->register('context', Routing\RequestContext::class);

//TODO implement cacheable routing (using router instead urlMatcher;
// https://stackoverflow.com/questions/31225578/how-to-cache-routes-when-using-symfony-routing-as-a-standalone/31229815
$containerBuilder->register('matcher', Routing\Matcher\UrlMatcher::class)
    ->setArguments([include __DIR__.'/routes.php', new Reference('context')])
;
$containerBuilder->register('request_stack', HttpFoundation\RequestStack::class);
$containerBuilder->register('controller_resolver', HttpKernel\Controller\ContainerControllerResolver::class)
    ->setArguments([new Reference('service_container')])
;
$containerBuilder->register('argument_resolver', HttpKernel\Controller\ArgumentResolver::class);

$containerBuilder->register('listener.router', HttpKernel\EventListener\RouterListener::class)
    ->setArguments([new Reference('matcher'), new Reference('request_stack')])
;
$containerBuilder->register('listener.response', HttpKernel\EventListener\ResponseListener::class)
    ->setArguments(['%charset%'])
;
$containerBuilder->register('listener.exception', HttpKernel\EventListener\ExceptionListener::class)
    ->setArguments(['error_controller:exception'])
;

$containerBuilder->register('dispatcher', EventDispatcher\EventDispatcher::class)
    ->addMethodCall('addSubscriber', [new Reference('listener.router')])
    ->addMethodCall('addSubscriber', [new Reference('listener.response')])
    ->addMethodCall('addSubscriber', [new Reference('listener.exception')])
;

$containerBuilder->register('twig.loader', FilesystemLoader::class)
    ->setArguments(['%twig.templates_dir%'])
;

$containerBuilder->register('twig', Environment::class)
    ->setArguments([new Reference('twig.loader'), [
        'cache' => '%twig.cache_dir%'
    ]])
;

$containerBuilder->register('annotation_driver',AnnotationDriver::class)
    ->setArguments(['%doctrine.orm.entity_path%', false])
    ->setFactory([new Reference('orm_configuration'), 'newDefaultAnnotationDriver'])
;

$containerBuilder->register('mapping_driver_chain', MappingDriverChain::class)
    ->addMethodCall('setDefaultDriver', [new Reference('annotation_driver')]
);

$containerBuilder->register('underscore_naming_strategy', UnderscoreNamingStrategy::class);

$containerBuilder->register('orm_configuration',Configuration::class)
    ->setArguments([
        '%doctrine.orm.auto_generate_proxies%',
        '%doctrine.orm.proxy_dir%'
    ])
    ->setFactory([Setup::class, 'createConfiguration'])
    ->addMethodCall('setMetadataDriverImpl', [new Reference('mapping_driver_chain')])
    ->addMethodCall('setNamingStrategy', [new Reference('underscore_naming_strategy')])
;

$containerBuilder->register('event_manager', EventManager::class);

$containerBuilder->register('entity_manager', EntityManager::class)
    ->setArguments([
        [
            'driver'   => '%doctrine.connection.driver%',
            'host'     => '%doctrine.connection.host%',
            'user'     => '%doctrine.connection.user%',
            'password' => '%doctrine.connection.password%',
            'dbname' => '%doctrine.connection.dbname%',
            'charset'  => '%doctrine.connection.charset%',
        ],
        new Reference('orm_configuration'),
        new Reference('event_manager')
    ])
    ->setFactory([EntityManager::class, 'create'])
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