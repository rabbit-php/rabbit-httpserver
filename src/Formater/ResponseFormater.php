<?php
declare(strict_types=1);

namespace Rabbit\HttpServer\Formater;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rabbit\Base\Helper\ArrayHelper;
use Rabbit\Web\AttributeEnum;
use Throwable;

/**
 * Class ResponseFormater
 * @package Rabbit\HttpServer\Formater
 */
class ResponseFormater implements IResponseFormatTool
{
    private array $formaters = [];
    private ?ResponseFormaterInterface $default = null;

    /**
     * The of header
     *
     * @var string
     */
    private string $headerKey = 'Content-type';

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws Throwable
     */
    public function format(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $contentType = $request->getHeaderLine($this->headerKey);
        $formaters = $this->mergeFormaters();
        $data = $response->getAttribute(AttributeEnum::RESPONSE_ATTRIBUTE);
        if (!isset($formaters[$contentType])) {
            if ($this->default === null) {
                $this->default = $formater = getDI(ResponseJsonFormater::class);
            } else {
                $formater = $this->default;
            }
        } else {
            /* @var ResponseFormaterInterface $formater */
            $formaterName = $formaters[$contentType];
            $formater = getDI($formaterName);
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
