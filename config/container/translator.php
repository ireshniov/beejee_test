<?php

use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Translator;

$containerBuilder->register('translator', Translator::class)
    ->setArguments(['%locale%'])
    ->addMethodCall('addLoader', ['php', new PhpFileLoader()])
    ->addMethodCall('addResource', ['php', '%translator.dir%/messages.%locale%.php', '%locale%'])
;



//// creates the Translator
//$translator = new Translator('en');
//// somehow load some translations into it
//$translator->addLoader('xlf', new XliffFileLoader());
//$translator->addResource(
//    'xlf',
//    __DIR__.'/path/to/translations/messages.en.xlf',
//    'en'
//);