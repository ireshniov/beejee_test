<?php

$container->setParameter('charset', getenv('CHARSET'));
$container->setParameter('cache.dir', __DIR__ . '/../var/cache');

$container->setParameter('locale', getenv('LOCALE'));

$container->setParameter('translator.dir', __DIR__ . '/../translations');

$container->setParameter('twig.cache_dir', __DIR__ . '/../var/cache/twig');
$container->setParameter('twig.template_dir', __DIR__ . '/../templates');
$container->setParameter('twig.default_form_theme', 'bootstrap_4_layout.html.twig');

$container->setParameter('doctrine.orm.auto_generate_proxies', getenv('DOCTRINE_AUTO_GENERATE_PROXIES'));
$container->setParameter('doctrine.orm.proxy_dir', __DIR__ . '/../var/cache/proxies');
$container->setParameter('doctrine.orm.entity_path', __DIR__ . '/../src/Entity');

$container->setParameter('doctrine.connection.driver', getenv('DB_DRIVER'));
$container->setParameter('doctrine.connection.host', getenv('DB_HOST'));
$container->setParameter('doctrine.connection.user', getenv('DB_USER'));
$container->setParameter('doctrine.connection.password', getenv('DB_PASSWORD'));
$container->setParameter('doctrine.connection.dbname', getenv('DB_NAME'));
$container->setParameter('doctrine.connection.charset', getenv('DB_CHARSET'));

$container->setParameter('migrations.name', 'Migrations');
$container->setParameter('migrations.namespace', 'App\Migrations');
$container->setParameter('migrations.table_name', 'doctrine_migration_versions');
$container->setParameter('migrations.column_length', 14);
$container->setParameter('migrations.dir', __DIR__ . '/../migrations');
$container->setParameter('migrations.set_all_or_nothing', true);
$container->setParameter('migrations.check_database_platform', true);
