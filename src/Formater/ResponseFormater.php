<?php

declare(strict_types=1);

namespace Rabbit\HttpServer\Formater;

use Throwable;
use Rabbit\Base\Helper\ArrayHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ResponseFormater
 * @package Rabbit\HttpServer\Formater
 */
class ResponseFormater implements IResponseFormatTool
{
    private array $formaters = [];
    private ?ResponseFormaterInterface $default = null;

    private string $headerKey = 'Content-type';

    public function format(ServerRequestInterface $request, ResponseInterface $response, string|array|object|float|int|bool|null &$data): ResponseInterface
    {
        $contentType = current(explode(';', $request->getHeaderLine($this->headerKey)));
        $formaters = $this->mergeFormaters();
        if (!isset($formaters[$contentType])) {
            if ($this->default === null) {
                $this->default = $formater = create(ResponseJsonFormater::class);
            } else {
                $formater = $this->default;
            }
        } else {
            $formaterName = $formaters[$contentType];
            $formater = create($formaterName);
        }

        return $formater->format($response, $data);
    }

    private function mergeFormaters(): array
    {
        return ArrayHelper::merge($this->defaultFormaters(), $this->formaters);
    }

    public function defaultFormaters(): array
    {
        return [
            'application/json' => ResponseJsonFormater::class,
            'application/xml' => ResponseXmlFormater::class,
        ];
    }
}
