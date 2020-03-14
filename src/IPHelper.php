<?php


namespace rabbit\httpserver;


use Psr\Http\Message\ServerRequestInterface;
use rabbit\helper\ArrayHelper;

/**
 * Class IPHelper
 * @package rabbit\web
 */
class IPHelper
{
    /**
     * 获取客户端Ip
     *
     * @param ServerRequestInterface $request
     * @return string
     */
    public function getClientIp(ServerRequestInterface $request)
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