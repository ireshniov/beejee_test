<?php

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;
use Symfony\Component\Security\Csrf\TokenStorage\SessionTokenStorage;

$containerBuilder->register('csrf.token_manager', CsrfTokenManager::class)
    ->setArguments([new UriSafeTokenGenerator(), new SessionTokenStorage(new Session())])
;
