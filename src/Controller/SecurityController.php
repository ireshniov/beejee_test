<?php

namespace App\Controller;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

/**
 * Class SecurityController
 * @package App\Controller
 */
class SecurityController implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function login()
    {
        //TODO access manager

        /** @var AuthenticationUtils $authenticationUtils */
        $authenticationUtils = $this->container->get('authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        /** @var Environment $twigEnvironment */
        $twigEnvironment = $this->container->get('twig');

        return $twigEnvironment->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }
}