<?php

namespace Brera\Renderer;

class Layout
{
    /**
     * @var string
     */
    private $nodeName;

    /**
     * @var array
     */
    private $nodeAttributes;

    /**
     * @var mixed
     */
    private $nodeValue;

    /**
     * @param $nodeName
     * @param array $nodeAttributes
     * @param mixed $nodeValue
     * @internal param array $attributes
     * @internal param string $name
     */
    private function __construct($nodeName, array $nodeAttributes, $nodeValue)
    {
        $this->nodeName = $nodeName;
        $this->nodeAttributes = $nodeAttributes;
        $this->nodeValue = $nodeValue;
    }

    /**
     * @param array $layoutArray
     * @return Layout
     */
    public static function fromArray(array $layoutArray)
    {
        $rootElement = self::getRootElement($layoutArray);
        $layoutArray = array_merge(['nodeName' => '', 'attributes' => [], 'value' => null], $rootElement);

        return new self($layoutArray['nodeName'], $layoutArray['attributes'], self::getValue($layoutArray['value']));
    }

    /**
     * @return string
     */
    public function getNodeName()
    {
        return $this->nodeName;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->nodeAttributes;
    }

    /**
     * @param string $attributeCode
     * @return mixed
     */
    public function getAttribute($attributeCode)
    {
        if (!array_key_exists($attributeCode, $this->nodeAttributes)) {
            return null;
        }

        return $this->nodeAttributes[$attributeCode];
    }

    /**
     * @return mixed
     */
    public function getNodeValue()
    {
        return $this->nodeValue;
    }

    /**
     * @param array $layout
     * @throws RootElementOfLayoutMustBeAnArrayException
     * @return mixed
     */
    private static function getRootElement(array $layout)
    {
        $rootElement = array_shift($layout);

        if (!is_array($rootElement)) {
            throw new RootElementOfLayoutMustBeAnArrayException();
        }

        return $rootElement;
    }

    /**
     * @param mixed $layout
     * @return mixed
     */
    private static function getValue($layout)
    {
        if (!is_array($layout)) {
            return $layout;
        }

        $values = [];

        foreach ($layout as $element) {
            $element = array_merge(['nodeName' => '', 'attributes' => [], 'value' => null], $element);
            $values[] = new self($element['nodeName'], $element['attributes'], self::getValue($element['value']));
        }

        return $values;
    }
}
