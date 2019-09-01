<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class NotFoundException
 * @package App\Exception
 */
class NotFoundException extends HttpException
{
    public function __construct($message = "Not found", $code = 404, \Exception $previous = null)
    {
        parent::__construct($code, $message, $previous);
    }
}
