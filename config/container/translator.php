<?php

use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Translator;

$vendorDirectory = realpath(__DIR__.'/../../vendor');
$vendorFormDirectory = $vendorDirectory.'/symfony/form';
$vendorValidatorDirectory = $vendorDirectory.'/symfony/validator';

$containerBuilder->register('translator', Translator::class)
    ->setArguments(['%locale%'])
    ->addMethodCall('addLoader', ['php', new PhpFileLoader()])
    ->addMethodCall('addLoader', ['xlf', new XliffFileLoader()])

    ->addMethodCall('addResource', ['php', '%translator.dir%/messages.%locale%.php', '%locale%'])
    ->addMethodCall('addResource', [
        'xlf',
        $vendorFormDirectory.'/Resources/translations/validators.%locale%.xlf',
        '%locale%',
        'validators'
    ])
    ->addMethodCall('addResource', [
        'xlf',
        $vendorValidatorDirectory.'/Resources/translations/validators.%locale%.xlf',
        '%locale%',
        'validators'
    ])
;
