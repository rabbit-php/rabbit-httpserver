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
    /**
     * @var bool whether to interpret objects implementing the [[\Traversable]] interface as arrays.
     * Defaults to `true`.
     */
    public bool $useTraversableAsArray = true;
    /**
     * @var bool if object tags should be added
     */
    public bool $useObjectTags = true;
    /**
     * @var string the Content-Type header for the response
     */
    private string $contentType = 'application/xml';
    /**
     * @var string the XML version
     */
    private string $version = '1.0';
    /**
     * @var string the name of the root element. If set to false, null or is empty then no root tag should be added.
     */
    private string $rootTag = 'response';
    /**
     * @var string the name of the elements that represent the array elements with numeric keys.
     */
    private string $itemTag = 'item';

    /**
     * @param ResponseInterface $response
     * @param $data
     * @return ResponseInterface
     */
    public function format(ResponseInterface $response, $data): ResponseInterface
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

    /**
     * @param DOMElement $element
     * @param mixed $data
     */
    protected function buildXml($element, $data)
    {
        if (is_array($data) ||
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

    /**
     * @param $name
     * @return string
     * @throws DOMException
     */
    protected function getValidXmlElementName($name)
    {
        if (empty($name) || is_int($name) || !$this->isValidXmlName($name)) {
            return $this->itemTag;
        }

        return $name;
    }

    /**
     * @param $name
     * @return bool
     * @throws DOMException
     */
    protected function isValidXmlName($name)
    {
        try {
            new DOMElement($name);
            return true;
        } catch (DOMException $e) {
            throw $e;
        }
    }

    /**
     * Formats scalar value to use in XML text node.
     *
     * @param int|string|bool|float $value a scalar value.
     * @return string string representation of the value.
     */
    protected function formatScalarValue($value)
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
