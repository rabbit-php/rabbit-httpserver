<?php
declare(strict_types=1);

namespace Rabbit\HttpServer\Exceptions;

/**
 * Class BadRequestHttpException
 * @package Rabbit\HttpServer
 */
class BadRequestHttpException extends HttpException
{
    /**
     * BadRequestHttpException constructor.
     * @param null $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(400, $message, $code, $previous);
    }
}
