<?php
declare(strict_types=1);

namespace Rabbit\HttpServer;


use Psr\Http\Message\ServerRequestInterface;
use Rabbit\Base\Helper\ArrayHelper;

/**
 * Class IPHelper
 * @package Rabbit\HttpServer
 */
class IPHelper
{
    /**
     * 获取客户端Ip
     *
     * @param ServerRequestInterface $request
     * @return string
     */
    public static function getClientIp(ServerRequestInterface $request)
    {
        if ($ip = $request->getHeaderLine('X-REAL-IP')) {
            return $ip;
        } elseif ($ip = $request->getHeaderLine('X-FORWARDED-FOR')) {
            return $ip;
        } else {
            return ArrayHelper::getValue($request->getServerParams(), 'remote_addr', '127.0.0.1');
        }
    }
}