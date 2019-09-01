<?php

use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\DependencyInjection\Reference;

$containerBuilder->register('annotation_driver',AnnotationDriver::class)
    ->setArguments(['%doctrine.orm.entity_path%', false])
    ->setFactory([new Reference('orm_configuration'), 'newDefaultAnnotationDriver'])
;

$containerBuilder->register('mapping_driver_chain', MappingDriverChain::class)
    ->addMethodCall('setDefaultDriver', [new Reference('annotation_driver')])
;

$containerBuilder->register('orm_configuration',Configuration::class)
    ->setArguments([
        '%doctrine.orm.auto_generate_proxies%',
        '%doctrine.orm.proxy_dir%'
    ])
    ->setFactory([Setup::class, 'createConfiguration'])
    ->addMethodCall('setMetadataDriverImpl', [new Reference('mapping_driver_chain')])
    ->addMethodCall('setNamingStrategy', [new UnderscoreNamingStrategy()])
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