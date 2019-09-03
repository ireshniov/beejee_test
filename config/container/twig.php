<?php

use Symfony\Bridge\Twig\AppVariable;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Bridge\Twig\Extension\SecurityExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

$appVariableReflection = new ReflectionClass(AppVariable::class);
$vendorTwigBridgeDirectory = dirname($appVariableReflection->getFileName());

$containerBuilder->register('twig.loader', FilesystemLoader::class)
    ->setArguments([
        [
            '%twig.template_dir%',
            $vendorTwigBridgeDirectory.'/Resources/views/Form',
        ]
    ])
;

$containerBuilder->register('app', AppVariable::class)
    ->addMethodCall('setTokenStorage', [new Reference('token_storage')])
    ->addMethodCall('setRequestStack', [new Reference('request_stack')])
;

$containerBuilder->register('twig', Environment::class)
    ->setArguments([new Reference('twig.loader'), [
//        'cache' => '%twig.cache_dir%'
    ]])
    ->addMethodCall('addGlobal', ['locale', '%locale%'])
    ->addMethodCall('addGlobal', ['app', new Reference('app')])
    ->addMethodCall('addRuntimeLoader', [new FactoryRuntimeLoader([
        FormRenderer::class => function () use ($containerBuilder) {
            /** @var TwigRendererEngine $formEngine */
            $formEngine = $containerBuilder->get('twig.renderer_engine');

            /** @var CsrfTokenManager $csrfManager */
            $csrfManager = $containerBuilder->get('csrf_token_manager');

            return new FormRenderer($formEngine, $csrfManager);
        },
    ])])
    ->addMethodCall('addExtension', [new FormExtension()])
    ->addMethodCall('addExtension', [new Reference('twig.extension.translation')])
    ->addMethodCall('addExtension', [new Reference('twig.extention.routing')])
    ->addMethodCall('addExtension', [new Reference('twig.extention.security')])
;

$containerBuilder->register('twig.renderer_engine', TwigRendererEngine::class)
    ->setArguments([
        ['%twig.default_form_theme%'],
        new Reference('twig')
    ])
;

$containerBuilder->register('twig.extension.translation', TranslationExtension::class)
    ->setArguments([new Reference('translator')])
;

$containerBuilder->register('twig.extention.routing', RoutingExtension::class)
    ->addArgument(new Reference('url_generator'))
;

$containerBuilder->register('twig.extention.security', SecurityExtension::class)
    ->addArgument(new Reference('authorization_checker'))
;