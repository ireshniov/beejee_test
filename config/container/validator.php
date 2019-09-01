<?php

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\RecursiveValidator;


$containerBuilder->register('validator', RecursiveValidator::class)
    ->setFactory([Validation::class, 'createValidator'])
;
