<?php

use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\Reference;

$containerBuilder->register('logger.formatter.logstash', LogstashFormatter::class)
    ->setArguments(['%service.name%'])
;

$containerBuilder->register('logger.handler.stream', StreamHandler::class)
    ->setArguments([
        '%logger.dir%/app.log',
        Monolog\Logger::DEBUG
    ])
    ->addMethodCall('setFormatter', [new Reference('logger.formatter.logstash')])
;

$containerBuilder->register('logger', Logger::class)
    ->setArguments(['%service.name%'])
    ->addMethodCall('pushHandler', [new Reference('logger.handler.stream')])
;