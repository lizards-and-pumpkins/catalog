<?php

namespace Brera\Renderer;

class Layout
{
    /**
     * @var string[]
     */
    private $nodeAttributes;

    /**
     * @var mixed
     */
    private $nodeValue;

    /**
     * @param array $nodeAttributes
     * @param mixed $nodeValue
     * @internal param array $attributes
     * @internal param string $name
     */
    private function __construct(array $nodeAttributes, $nodeValue)
    {
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
        $layoutArray = array_merge(['attributes' => [], 'value' => null], $rootElement);

        return new self($layoutArray['attributes'], self::getValue($layoutArray['value']));
    }

    /**
     * @return string[]
     */
    public function getAttributes()
    {
        return $this->nodeAttributes;
    }

    /**
     * @param string $attributeCode
     * @return null|string
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
            $element = array_merge(['attributes' => [], 'value' => null], $element);
            $values[] = new self($element['attributes'], self::getValue($element['value']));
        }

        return $values;
    }
}
