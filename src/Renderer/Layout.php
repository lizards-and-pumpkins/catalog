<?php

namespace Brera\Renderer;

class Layout
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var mixed
     */
    private $payload;

    /**
     * @param string $name
     * @param array $attributes
     * @param mixed $payload
     */
    private function __construct($name, array $attributes, $payload)
    {
        $this->name = $name;
        $this->attributes = $attributes;
        $this->payload = $payload;
    }

    /**
     * @param array $layoutArray
     * @return Layout
     */
    public static function fromArray(array $layoutArray)
    {
        $rootElement = self::getRootElement($layoutArray);
        $layoutArray = array_merge(['name' => '', 'attributes' => [], 'value' => null], $rootElement);

        return new self($layoutArray['name'], $layoutArray['attributes'], self::getValue($layoutArray['value']));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $attributeCode
     * @return mixed
     */
    public function getAttribute($attributeCode)
    {
        if (!array_key_exists($attributeCode, $this->attributes)) {
            return null;
        }

        return $this->attributes[$attributeCode];
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
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
            $element = array_merge(['name' => '', 'attributes' => [], 'value' => null], $element);
            $values[] = new self($element['name'], $element['attributes'], self::getValue($element['value']));
        }

        return $values;
    }
}
