<?php
declare(strict_types=1);

namespace Rabbit\HttpServer\Exceptions;
/**
 * Class NotFoundHttpException
 * @package Rabbit\HttpServer\Exceptions
 */
class NotFoundHttpException extends HttpException
{
    /**
     * NotFoundHttpException constructor.
     * @param null $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(404, $message, $code, $previous);
    }
}
