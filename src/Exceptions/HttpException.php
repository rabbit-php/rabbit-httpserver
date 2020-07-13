<?php
declare(strict_types=1);

namespace Rabbit\HttpServer\Exceptions;


use Rabbit\Base\Core\UserException;
use Rabbit\HttpServer\Response;

/**
 * Class HttpException
 * @package Rabbit\Web
 */
class HttpException extends UserException
{
    /**
     * @var int HTTP status code, such as 403, 404, 500, etc.
     */
    public int $statusCode;


    /**
     * HttpException constructor.
     * @param $status
     * @param null $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($status, $message = null, $code = 0, \Exception $previous = null)
    {
        $this->statusCode = $status;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        if (isset(Response::$phrases[$this->statusCode])) {
            return Response::$phrases[$this->statusCode];
        }

        return 'Error';
    }
}
