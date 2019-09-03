<?php

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\Form\Forms;

$containerBuilder->register('form_factory_builder', FormFactoryBuilder::class)
    ->setFactory([Forms::class, 'createFormFactoryBuilder'])
    ->addMethodCall('addExtension', [new HttpFoundationExtension()])
    ->addMethodCall('addExtension', [new Reference('form.extention.csrf')])
    ->addMethodCall('addExtension', [new Reference('form.extention.validator')])
;

$containerBuilder->register('form_factory', FormFactory::class)
    ->setFactory([new Reference('form_factory_builder'), 'getFormFactory'])
;

$containerBuilder->register('form.extention.csrf', CsrfExtension::class)
    ->addArgument(new Reference('csrf_token_manager'))
;

$containerBuilder->register('form.extention.validator', ValidatorExtension::class)
    ->addArgument(new Reference('validator'))
;