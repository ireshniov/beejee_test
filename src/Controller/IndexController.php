<?php


namespace App\Controller;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class IndexController
 * @package App\Controller
 */
class IndexController implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Request $request
     * @param string|null $name
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function hello(Request $request, ?string $name): string
    {
        /** @var Environment $twigEnvironment */
        $twigEnvironment = $this->container->get('twig');

        return $twigEnvironment->render('index/hello.html.twig', ['name' => $name]);
    }
}