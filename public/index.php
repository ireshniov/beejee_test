<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../.env');

/** @var ContainerBuilder $container */
$container = include __DIR__ . '/../config/container/container.php';
require_once __DIR__ .'/../config/services.php';
require_once __DIR__ .'/../config/parameters.php';

$request = Request::createFromGlobals();

/** @var HttpCache $framework */
$framework = $container->get('framework.cache');

$response = $framework->handle($request);

$response->send();
