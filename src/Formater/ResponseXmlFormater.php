<?php

declare(strict_types=1);

namespace Rabbit\HttpServer\Formater;

use DOMText;
use DOMElement;
use DOMDocument;
use DOMException;
use Rabbit\Base\Contract\ArrayAble;
use Rabbit\Base\Helper\StringHelper;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ResponseXmlFormater
 * @package Rabbit\HttpServer\Formater
 */
class ResponseXmlFormater implements ResponseFormaterInterface
{
    public bool $useTraversableAsArray = true;

    public bool $useObjectTags = true;

    private string $contentType = 'application/xml';

    private string $version = '1.0';

    private string $rootTag = 'response';

    private string $itemTag = 'item';

    public function format(ResponseInterface $response, string|array|object|float|int|bool|null &$data): ResponseInterface
    {
        // Headers
        $response = $response->withoutHeader('Content-Type')->withAddedHeader('Content-Type', $this->contentType);
        if ($data !== null) {
            $dom = new DOMDocument($this->version, 'utf-8');
            if (!empty($this->rootTag)) {
                $root = new DOMElement($this->rootTag);
                $dom->appendChild($root);
                $this->buildXml($root, $data);
            } else {
                $this->buildXml($dom, $data);
            }
            $body = $response->getBody();
            $body->seek(0);
            $body->write($dom->saveXML());
        }
        return $response;
    }

    protected function buildXml(DOMDocument|DOMElement $element, &$data)
    {
        if (
            is_array($data) ||
            ($data instanceof \Traversable && $this->useTraversableAsArray && !$data instanceof Arrayable)
        ) {
            foreach ($data as $name => $value) {
                if (is_int($name) && is_object($value)) {
                    $this->buildXml($element, $value);
                } elseif (is_array($value) || is_object($value)) {
                    $child = new DOMElement($this->getValidXmlElementName($name));
                    $element->appendChild($child);
                    $this->buildXml($child, $value);
                } else {
                    $child = new DOMElement($this->getValidXmlElementName($name));
                    $element->appendChild($child);
                    $child->appendChild(new DOMText($this->formatScalarValue($value)));
                }
            }
        } elseif (is_object($data)) {
            if ($this->useObjectTags) {
                $child = new DOMElement(StringHelper::basename(get_class($data)));
                $element->appendChild($child);
            } else {
                $child = $element;
            }
            if ($data instanceof Arrayable) {
                $this->buildXml($child, $data->toArray());
            } else {
                $array = [];
                foreach ($data as $name => $value) {
                    $array[$name] = $value;
                }
                $this->buildXml($child, $array);
            }
        } else {
            $element->appendChild(new DOMText($this->formatScalarValue($data)));
        }
    }

    protected function getValidXmlElementName(string|int $name)
    {
        if (empty($name) || is_int($name) || !$this->isValidXmlName($name)) {
            return $this->itemTag;
        }

        return $name;
    }

    protected function isValidXmlName(string|int $name)
    {
        try {
            new DOMElement($name);
            return true;
        } catch (DOMException $e) {
            throw $e;
        }
    }

    protected function formatScalarValue(int|string|bool|float $value)
    {
        if ($value === true) {
            return 'true';
        }
        if ($value === false) {
            return 'false';
        }
        if (is_float($value)) {
            return StringHelper::floatToString($value);
        }
        return (string)$value;
    }
}
