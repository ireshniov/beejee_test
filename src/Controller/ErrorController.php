<?php

namespace App\Controller;

use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ErrorController
 * @package App\Controller
 */
class ErrorController implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param FlattenException $exception
     * @return Response
     */
    public function exception(FlattenException $exception): Response
    {
        $msg = 'Something went wrong! ('.$exception->getMessage().')';

        return new Response($msg, $exception->getStatusCode());
    }
}