<?php

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\Form\Forms;

$containerBuilder->register('form_factory_builder', FormFactoryBuilder::class)
    ->setFactory([Forms::class, 'createFormFactoryBuilder'])
    ->addMethodCall('addExtension', [new HttpFoundationExtension()])
;

$containerBuilder->register('form_factory', FormFactory::class)
    ->setFactory([new Reference('form_factory_builder'), 'getFormFactory'])
;