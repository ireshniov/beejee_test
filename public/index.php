<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../.env');

$container = include __DIR__.'/../config/container.php';
require_once __DIR__ .'/../config/services.php';
require_once __DIR__ .'/../config/parameters.php';

$request = Request::createFromGlobals();

$response = $container->get('framework.cache')->handle($request);

$response->send();
