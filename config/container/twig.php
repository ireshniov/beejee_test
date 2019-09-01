<?php

use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

$appVariableReflection = new \ReflectionClass('\Symfony\Bridge\Twig\AppVariable');
$vendorTwigBridgeDirectory = dirname($appVariableReflection->getFileName());

$containerBuilder->register('twig.loader', FilesystemLoader::class)
    ->setArguments([
        [
            '%twig.template_dir%',
            $vendorTwigBridgeDirectory.'/Resources/views/Form',
        ]
    ])
;

$containerBuilder->register('twig', Environment::class)
    ->setArguments([new Reference('twig.loader'), [
        'cache' => '%twig.cache_dir%'
    ]])
;

$containerBuilder->register('twig.renderer_engine', TwigRendererEngine::class)
    ->setArguments([
        ['%twig.default_form_theme%'],
        new Reference('twig')
    ])
;

$containerBuilder->getDefinition('twig')
    ->addMethodCall('addRuntimeLoader', [new FactoryRuntimeLoader([
        FormRenderer::class => function () use ($containerBuilder) {
            /** @var TwigRendererEngine $formEngine */
            $formEngine = $containerBuilder->get('twig.renderer_engine');

            /** @var CsrfTokenManager $csrfManager */
            $csrfManager = $containerBuilder->get('csrf.token_manager');

            return new FormRenderer($formEngine, $csrfManager);
        },
    ])])
;

$containerBuilder->register('twig.extension.translation', TranslationExtension::class)
    ->setArguments([new Reference('translator')])
;

$containerBuilder->getDefinition('twig')
    ->addMethodCall('addExtension', [new FormExtension()])
    ->addMethodCall('addExtension', [new Reference('twig.extension.translation')])
;