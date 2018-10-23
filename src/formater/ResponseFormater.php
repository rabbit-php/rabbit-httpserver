<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/15
 * Time: 14:01
 */

namespace rabbit\httpserver\formater;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use rabbit\core\ObjectFactory;
use rabbit\helper\ArrayHelper;
use rabbit\server\AttributeEnum;

/**
 * Class ResponseFormater
 * @package rabbit\httpserver\formater
 */
class ResponseFormater implements IResponseFormatTool
{
    /**
     * @var ResponseFormaterInterface[]
     */
    private $formaters;

    /**
     * @var ResponseFormaterInterface
     */
    private $default = ResponseJsonFormater::class;

    /**
     * The of header
     *
     * @var string
     */
    private $headerKey = 'Content-type';

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws \Exception
     */
    public function format(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $contentType = $request->getHeaderLine($this->headerKey);
        $formaters = $this->mergeFormaters();
        $data = $response->getAttribute(AttributeEnum::RESPONSE_ATTRIBUTE);
        if (!isset($formaters[$contentType])) {
            if (is_string($this->default)) {
                $formater = ObjectFactory::get($this->default);
            } else {
                $formater = $this->default;
            }
        } else {
            /* @var ResponseFormatInterface $formater */
            $formaterName = $formaters[$contentType];
            $formater = ObjectFactory::get($formaterName);
        }

        return $formater->format($response, $data);
    }

    /**
     * @return array
     */
    private function mergeFormaters(): array
    {
        return ArrayHelper::merge($this->formaters, $this->defaultFormaters());
    }

    /**
     * Default parsers
     *
     * @return array
     */
    public function defaultFormaters(): array
    {
        return [
            'application/json' => ResponseJsonFormater::class,
            'application/xml' => ResponseXmlFormater::class,
        ];
    }

}