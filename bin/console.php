#!/usr/bin/env php

<?php

use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand;
use Doctrine\Migrations\Tools\Console\Helper\ConfigurationHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand;
use Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand;
use Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand;
use Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand;
use Doctrine\ORM\Tools\Console\Command\InfoCommand;
use Doctrine\ORM\Tools\Console\Command\MappingDescribeCommand;
use Doctrine\ORM\Tools\Console\Command\RunDqlCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Dotenv\Dotenv;

if (false === in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
    echo 'Warning: The console should be invoked via the CLI version of PHP, not the '.PHP_SAPI.' SAPI'.PHP_EOL;
}

set_time_limit(0);

require __DIR__. '/../vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load( __DIR__. '/../.env');

/** @var ContainerBuilder $container */
$container = include __DIR__ . '/../config/container/container.php';
require_once __DIR__ .'/../config/services.php';
require_once __DIR__ .'/../config/parameters.php';

try {
    /** @var EntityManager $entityManager */
    $entityManager = $container->get('entity_manager');
} catch (Exception $exception) {
}

$migrationConfiguration = new Configuration($entityManager->getConnection());
$migrationConfiguration->setName($container->getParameter('migrations.name'));
$migrationConfiguration->setMigrationsNamespace($container->getParameter('migrations.namespace'));
$migrationConfiguration->setMigrationsTableName($container->getParameter('migrations.table_name'));
$migrationConfiguration->setMigrationsColumnLength($container->getParameter('migrations.column_length'));
$migrationConfiguration->setMigrationsDirectory($container->getParameter('migrations.dir'));
$migrationConfiguration->setAllOrNothing($container->getParameter('migrations.set_all_or_nothing'));
$migrationConfiguration->setCheckDatabasePlatform($container->getParameter('migrations.check_database_platform'));

$configurationHelper = new ConfigurationHelper($entityManager->getConnection(), $migrationConfiguration);

$questionHelper = new QuestionHelper();

$entityManagerHelper = new EntityManagerHelper($entityManager);

$connectionHelper = new ConnectionHelper($entityManager->getConnection());

$helperSet = new HelperSet();
$helperSet->set($connectionHelper, 'db');
$helperSet->set($questionHelper, 'question');
$helperSet->set($configurationHelper, 'configuration');
$helperSet->set($entityManagerHelper, 'em');

$cli = new Application(
    getenv('SERVICE_NAME'),
    getenv('SERVICE_VERSION')
);
$cli->setCatchExceptions(true);

$cli->setHelperSet($helperSet);

$cli->addCommands(array(
    // Migrations Commands
    new ExecuteCommand(),
    new GenerateCommand(),
    new LatestCommand(),
    new MigrateCommand(),
    new StatusCommand(),
    new VersionCommand(),
    new DiffCommand(),

    new MetadataCommand(),
    new ResultCommand(),
    new QueryCommand(),
    new CreateCommand(),
    new UpdateCommand(),
    new DropCommand(),

    new EnsureProductionSettingsCommand(),
    new GenerateRepositoriesCommand(),
    new GenerateEntitiesCommand(),
    new GenerateProxiesCommand(),
    new ConvertMappingCommand(),
    new RunDqlCommand(),
    new ValidateSchemaCommand(),
    new InfoCommand(),
    new MappingDescribeCommand(),
));

try {
    $cli->run();
} catch (Exception $exception) {
}