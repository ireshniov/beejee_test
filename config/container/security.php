<?php

use App\Security\AccessDeniedHandler;
use App\Security\LoginFormAuthenticator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\Provider\AnonymousAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;
use Symfony\Component\Security\Csrf\TokenStorage\SessionTokenStorage;
use Symfony\Component\Security\Guard\Firewall\GuardAuthenticationListener;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Guard\Provider\GuardAuthenticationProvider;
use Symfony\Component\Security\Http\AccessMap;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Firewall;
use Symfony\Component\Security\Http\Firewall\AccessListener;
use Symfony\Component\Security\Http\Firewall\AnonymousAuthenticationListener;
use Symfony\Component\Security\Http\Firewall\ContextListener;
use Symfony\Component\Security\Http\Firewall\ExceptionListener;
use Symfony\Component\Security\Http\Firewall\LogoutListener;
use Symfony\Component\Security\Http\FirewallMap;
use Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategy;

$containerBuilder->register('firewall', Firewall::class)
    ->setArguments([new Reference('firewall.map'), new Reference('dispatcher')])
;

$containerBuilder
    ->register('firewall.map', FirewallMap::class)
    ->addMethodCall('add', [
        new RequestMatcher('^/'),
        [
            new Reference('firewall.access_listener'),
            new Reference('firewall.context_listener'),
            new Reference('firewall.anonymous_authentication_listener'),
            new Reference('firewall.guard_authentication_listener'),
        ],
        new Reference('firewall.exception_listener'),
        new Reference('firewall.logout_listener'),
    ])
;

$containerBuilder->register('token_storage', TokenStorage::class);

$containerBuilder->register('session.authentication_strategy', SessionAuthenticationStrategy::class)
    ->addArgument(SessionAuthenticationStrategy::MIGRATE)
;

$containerBuilder->register('guard_authenticator_handler', GuardAuthenticatorHandler::class)
    ->setArguments([
        new Reference('token_storage'),
        new Reference('dispatcher'),
    ])
    ->addMethodCall('setSessionAuthenticationStrategy', [new Reference('session.authentication_strategy')])
;

$containerBuilder->register('user_checker', UserChecker::class);

$containerBuilder->register('user_provider.in_memory', InMemoryUserProvider::class)
    ->addArgument([
        'admin' => [
            'password' => '123',
            'roles' => ['ROLE_ADMIN'],
        ],
    ])
;

$containerBuilder->register('csrf_token_generator.uri_safe', UriSafeTokenGenerator::class)
    ->addArgument(256)
;

$containerBuilder->register('csrf_token_storage.session', SessionTokenStorage::class)
    ->setArguments([new Reference('session')])
;

$containerBuilder->register('csrf_token_manager', CsrfTokenManager::class)
    ->setArguments([
        new Reference('csrf_token_generator.uri_safe'),
        new Reference('csrf_token_storage.session'),
        new Reference('request_stack'),
    ])
;

$containerBuilder->register('encoder.factory', EncoderFactory::class)
    ->addArgument([
        //User::class => new BCryptPasswordEncoder(10),
        User::class => new PlaintextPasswordEncoder(),
    ])
;

$containerBuilder->register('encoder.user_password', UserPasswordEncoder::class)
    ->addArgument(new Reference('encoder.factory'))
;

$containerBuilder->register('login_form_authenticator', LoginFormAuthenticator::class)
    ->setArguments([
        new Reference('url_generator'),
        new Reference('csrf_token_manager'),
        new Reference('encoder.user_password')
    ])
;

$containerBuilder->register('anonymous_authentication_provider', AnonymousAuthenticationProvider::class)
    ->setArguments(['%service.secret%'])
;

$containerBuilder->register('guard_authentication_provider', GuardAuthenticationProvider::class)
    ->setArguments([
        [
            new Reference('login_form_authenticator')
        ],
        new Reference('user_provider.in_memory'),
        'main',
        new Reference('user_checker'),
    ])
;

$containerBuilder->register('logout_success_handler.default', DefaultLogoutSuccessHandler::class)
    ->setArguments([
        new Reference('http_utils'),
        'task.list',
    ])
;

$containerBuilder->register('access_denied_handler', AccessDeniedHandler::class)
    ->setArguments([
        new Reference('token_storage'),
        new Reference('url_generator')
    ])
;

$containerBuilder->register('authentication_trust_resolver', AuthenticationTrustResolver::class);

$containerBuilder->register('voter.role', RoleVoter::class)
    ->addArgument('ROLE_')
;

$containerBuilder->register('voter.authenticated', AuthenticatedVoter::class)
    ->addArgument(new Reference('authentication_trust_resolver'))
;

$containerBuilder->register('access_decision_manager', AccessDecisionManager::class)
    ->setArguments([
        [
            new Reference('voter.role'),
            new Reference('voter.authenticated'),
        ],
        AccessDecisionManager::STRATEGY_AFFIRMATIVE,
        false,
        true
    ])
;

$containerBuilder->register('access_map', AccessMap::class)
    ->addMethodCall('add', [
        new RequestMatcher('^/tasks$'),
        ['IS_AUTHENTICATED_ANONYMOUSLY']
    ])
    ->addMethodCall('add', [
        new RequestMatcher('^/tasks/\d+/update$'),
        ['ROLE_ADMIN']
    ])
    ->addMethodCall('add', [
        new RequestMatcher('^/tasks/\d+/complete'),
        ['ROLE_ADMIN']
    ])
;

###Listeners

$containerBuilder->register('firewall.access_listener', AccessListener::class)
    ->setArguments([
        new Reference('token_storage'),
        new Reference('access_decision_manager'),
        new Reference('access_map'),
        new Reference('guard_authentication_provider')
    ])
;

$containerBuilder->register('firewall.anonymous_authentication_listener', AnonymousAuthenticationListener::class)
    ->setArguments([
        new Reference('token_storage'),
        '%service.secret%',
        new Reference('logger'),
        new Reference('anonymous_authentication_provider')
    ])
;

$containerBuilder->register('firewall.logout_listener', LogoutListener::class)
    ->setArguments([
        new Reference('token_storage'),
        new Reference('http_utils'),
        new Reference('logout_success_handler.default')
    ])
;

$containerBuilder->register('firewall.context_listener', ContextListener::class)
    ->setArguments([
        new Reference('token_storage'),
        [
            new Reference('user_provider.in_memory'),
        ],
        'main',
        new Reference('logger'),
        new Reference('dispatcher'),
        new Reference('authentication_trust_resolver')
    ])
;

$containerBuilder->register('firewall.guard_authentication_listener', GuardAuthenticationListener::class)
    ->setArguments([
        new Reference('guard_authenticator_handler'),
        new Reference('guard_authentication_provider'),
        'main',
        [
            new Reference('login_form_authenticator')
        ],
        new Reference('logger')
    ])
;

$containerBuilder->register('firewall.exception_listener', ExceptionListener::class)
    ->setArguments([
        new Reference('token_storage'),
        new Reference('authentication_trust_resolver'),
        new Reference('http_utils'),
        'main',
        new Reference('login_form_authenticator'),
        null,
        new Reference('access_denied_handler'),
        new Reference('logger')
    ])
;

$containerBuilder->register('authentication_utils', AuthenticationUtils::class)
    ->addArgument(new Reference('request_stack'))
;

$containerBuilder->register('authorization_checker', AuthorizationChecker::class)
    ->setArguments([
        new Reference('token_storage'),
        new Reference('guard_authentication_provider'),
        new Reference('access_decision_manager'),
    ])
;